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

class JavaResultService extends CommonService {
    const NETWORK_LATENCY = 5.52749; //value obtained from the baseline test to establish the average network latency in ms

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
            if ($line != "") {
                $result  = explode("|", $line);

                if (isset($result[1])) {
                    $time = new \DateTime();
                    $time->setTimestamp((int) $result[4]);

                    $threadRun = new ThreadRun();
                    $threadRun->start = (int) $result[1];
                    $threadRun->end = (int) $result[2];
                    $threadRun->serverDuration = ((int)$result[3]);
                    $threadRun->time = $time;
                    $threadRun->localDuration = $result[5];

                    if ($result[0] != "") {
                        $thread->runs->add($threadRun);
                        $thread->name = $result[0];
                    }
                }
            }
        }
        fclose($fh);

        return $thread;
    }

    public function mergeResultFiles(OutputInterface $output, $experimentName) {
        foreach (Finder::findDirectories("[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]")->from(APP_DIR."/../results/". $experimentName) as $resultDir) {
            /** @var SplFileInfo $resultDir  */
            $resultKey = $resultDir->getFilename();
            $fullResultFile = APP_DIR."/../results/merged_results/".$resultKey."/".$experimentName.".txt";
            $fullResult = "";
            foreach (Finder::findFiles("$resultKey-*.txt")->from($resultDir->getRealPath()) as $resultFile) {
                /** @var SplFileInfo $resultFile  */
                $result = FileSystem::read($resultFile->getRealPath());
                $fullResult.=$result;
            }
            FileSystem::write($fullResultFile, $fullResult);
        }
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

    protected $vmstat = [];

    protected function processVmstat($o, $statFile) {
        $statFileName = APP_DIR."/../results/" . $statFile;
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

    protected function generateLatexFile(string $name, $results) {
        $latexFileName = APP_DIR."/../results/graph_txt/".$name.".txt";
        $list = array();
        $i = 0;
        $list[$i] = ["X", "Threads", "ServerTime", "TotalTime"];
        /** @var Experiment $result */
        foreach ($results as $result) {
            $i++;
            $list[$i] = [
                $i,
                $result->threadCount,
                $result->averageServerTime,
                $result->averageTotalTime - self::NETWORK_LATENCY
            ];
        }

        //label the line
        //$list[$i][2] = $name;

        //store into a file
        $fp = fopen($latexFileName, 'w');

        foreach ($list as $fields) {
            fputcsv($fp, $fields, " ");
        }

        fclose($fp);
    }

    protected function writeResultsToFile(string $name, $results) {
        $resultsFileName = APP_DIR."/../results/graph_csvs/".$name.".csv";

        $list = array();
        $list[0] = ["Content Length", "Thread count", "Sleep (ms)", "Server time (ms)", "Total time (ms)", "Network Latency (ms)", "Free", "Buff", "Cache", "User", "System", "Idle"];
        /** @var Experiment $result */
        foreach ($results as $result) {
            $list[$result->threadCount] = [
                $result->contentLength,
                $result->threadCount,
                $result->sleep / 1000,
                $result->averageServerTime,
                $result->averageTotalTime,
                $result->averageNetworkLatency,
                $result->averageVmStat->free,
                $result->averageVmStat->buff,
                $result->averageVmStat->cache,
                $result->averageVmStat->user,
                $result->averageVmStat->system,
                $result->averageVmStat->idle
            ];
        }

        $fp = fopen($resultsFileName, 'w');

        foreach ($list as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);
    }

    public function calculateAverages(OutputInterface $o, $experimentName, $statFile) {
        $runs = [];
        $this->processVmstat($o, $statFile);
        foreach (Finder::findDirectories("[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]")->from(APP_DIR."/../results/". $experimentName) as $resultDir) {
            /** @var SplFileInfo $resultDir  */
            $configFileName = $resultDir->getRealPath()."/config.json";
            $experiment = new Experiment();
            $experiment->name = $experimentName;

            try {
                $configFile = FileSystem::read($configFileName);
                //fix config file that was incorrectly generated during the experiment
                $configFile = str_replace("\"length", ",\"length", $configFile);
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
                //$experiment = $this->attachVmstats($experiment);
                //$this->experimentRepository->persistAndFlush($experiment, true);
                $runs[] = $experiment;
            } catch (\Exception $e) {
                //TODO - error handling into output here
                $o->writeln($e->getMessage());
            }
        }

        //Sort results in ascending order by thread count
        usort($runs, function($a, $b) {
            return ($a->threadCount < $b->threadCount) ? -1 : 1;
        });

        $this->writeResultsToFile($experimentName, $runs);
        $this->generateLatexFile($experimentName, $runs);
        return $runs;
    }
}
