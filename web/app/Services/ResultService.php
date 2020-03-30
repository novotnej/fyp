<?php
namespace App\Services;


use App\Model\Experiment;
use App\Model\Thread;
use App\Model\ThreadRun;
use App\Model\Vmstat;
use App\Repositories\ExperimentRepository;
use App\Repositories\ThreadRepository;
use App\Repositories\ThreadRunRepository;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use SplFileInfo;
use Symfony\Component\Console\Output\OutputInterface;

class ResultService extends CommonService {
    /** @var OutputInterface */
    protected $output;
    /** @var ExperimentRepository  */
    protected $experimentRepository;
    /** @var ThreadRunRepository  */
    protected $threadRunRepository;
    /** @var ThreadRepository  */
    protected $threadRepository;

    /**
     * ResultService constructor.
     * @param ExperimentRepository $experimentRepository
     * @param ThreadRunRepository $threadRunRepository
     * @param ThreadRepository $threadRepository
     */
    public function __construct(ExperimentRepository $experimentRepository, ThreadRunRepository $threadRunRepository, ThreadRepository $threadRepository) {
        $this->experimentRepository = $experimentRepository;
        $this->threadRepository = $threadRepository;
        $this->threadRunRepository = $threadRunRepository;
    }

    protected function getThread(SplFileInfo $resultFile) {
        $thread = new Thread();

        $fh = fopen($resultFile->getRealPath(), "r");
        while(!feof($fh)) {
            $line = fgets($fh);
            $result  = explode("|", $line);

            if (isset($result[1])) {
                $time = new \DateTime();
                $time->setTimestamp((int) $result[4]);

                $threadRun = new ThreadRun();
                $threadRun->start = (int) $result[1];
                $threadRun->end = (int) $result[2];
                $threadRun->serverDuration = ((int)$result[3])/1000;
                $threadRun->time = $time;
                $threadRun->localDuration = (int)str_replace(PHP_EOL, "", $result[5]);

                if ($result[0] != "") {
                    $thread->runs->add($threadRun);
                    $thread->name = $result[0];
                }
            }
        }
        fclose($fh);

        return $thread;
    }

    protected function calculateThreads(Experiment $experiment, SplFileInfo $resultDir) {
        $resultKey = $resultDir->getFilename();

        foreach (Finder::findFiles("$resultKey-*.txt")->from($resultDir->getRealPath()) as $resultFile) {
            /** @var SplFileInfo $resultFile  */
            $thread = $this->getThread($resultFile);
            $thread->experiment = $experiment;
            $thread = $this->threadRepository->persist($thread);
            $experiment->threads->add($thread);
        }
        $this->threadRepository->flush();
        return $experiment;
    }

    function parserVmstat($lines = '') {
        function isEmpty($value) {
            return (strlen($value)) != 0;
        }
        if (strlen($lines) == 0) {
            exec('vmstat', $res);
        } else {
            $res = $lines;
        }
        $keys = explode(' ', $res[1]);
        $values = explode(' ', $res[2]);
        $keys = array_values(array_filter( $keys, 'isEmpty'));

        $parameters = array();
        foreach($keys as $key => $value) {
            $parameters[$value] = $values[$key];
        }
        return $parameters;
    }

    protected $vmstat = [];

    protected function processVmstat($o) {
        $statFileName = APP_DIR."/../results/vmstat.txt";
        $fh = fopen($statFileName, "r");
        while(!feof($fh)) {
            $line = fgets($fh);
            $isHeader = strpos($line, "memory") !== false || strpos($line, "buff") !== false;
            if (!$isHeader) {
                $values = array_filter(explode(' ', $line), function($value) { return !is_null($value) && $value !== ''; });

                $ts = $values[0];
                if (count($values) > 0) {
                    $this->vmstat[$ts] = $values;
                    //$o->writeln(implode(";", $values));
                }
            }
        }

        fclose($fh);
    }

    protected function attachVmstats(Experiment $experiment) {
        $firstTs = $experiment->time->getTimestamp();
        $lastTs = $experiment->timeEnd->getTimestamp();

        //take each second between first and last timestamp in the experiment and take that vmstat reading, if exists
        for ($i = $firstTs; $i <= $lastTs; $i++) {
            if (isset($this->vmstat[$i])) {
                $vmstat = new Vmstat();
                $vmstat->timestamp = (int)$this->vmstat[$i][0];
                $vmstat->free = (int)$this->vmstat[$i][11];
                $vmstat->buff = (int)$this->vmstat[$i][13];
                $vmstat->cache = (int)$this->vmstat[$i][14];
                $vmstat->user = (int)$this->vmstat[$i][37];
                $vmstat->system = (int)$this->vmstat[$i][38];
                $vmstat->idle = (int)$this->vmstat[$i][40];
                $experiment->vmstats->add($vmstat);
            }
        }
        return $experiment;
    }

    protected function writeResultsToFile(string $name, $results) {
        $statFileName = APP_DIR."/../results/".$name.".csv";

        $list = array();
        $list[0] = ["Content Length", "Server time (ms)", "Total time (ms)", "Network Latency (ms)", "Free", "Buff", "Cache", "User", "System", "Idle"];
        /** @var Experiment $result */
        foreach ($results as $result) {
            $list[$result->contentLength] = [
                $result->contentLength,
                $result->averageServerTime/1000,
                $result->averageTotalTime/1000,
                $result->averageNetworkLatency/1000,
                $result->averageVmStat->free,
                $result->averageVmStat->buff,
                $result->averageVmStat->cache,
                $result->averageVmStat->user,
                $result->averageVmStat->system,
                $result->averageVmStat->idle
            ];
        }

        ksort($list);

        $fp = fopen($statFileName, 'w');

        foreach ($list as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);
    }

    public function calculateAverages(OutputInterface $o, $experimentName) {
        $runs = [];
        $this->processVmstat($o);
        foreach (Finder::findDirectories("[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]")->from(APP_DIR."/../results/". $experimentName) as $resultDir) {
            /** @var SplFileInfo $resultDir  */
            $configFileName = $resultDir->getRealPath()."/config.json";
            $experiment = new Experiment();
            $experiment->name = $experimentName;

            try {
                $configFile = FileSystem::read($configFileName);
                $config = @json_decode($configFile);
                if (!$config) {
                    throw new \Exception("Malformatted config file");
                }
                $dt = new \DateTime();
                $dt->setTimestamp($config->timestamp);
                $experiment->sleep = $config->sleep;
                $experiment->contentLength = $config->length;
                $experiment->threadCount = $config->threads;
                $experiment->iterations = $config->iterations;
                $experiment->time = $dt;
                $experiment = $this->calculateThreads($experiment, $resultDir);
                $experiment = $this->attachVmstats($experiment);
                $this->experimentRepository->persistAndFlush($experiment, true);
                $runs[] = $experiment;
            } catch (\Exception $e) {
                //TODO - error handling into output here
                $o->writeln($e->getMessage());
            }
        }

        $this->writeResultsToFile($experimentName, $runs);

        return $runs;
    }
}
