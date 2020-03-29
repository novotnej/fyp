<?php
namespace App\Services;


use App\Model\Experiment;
use App\Model\Thread;
use App\Model\ThreadRun;
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
                $threadRun->serverDuration = (int)$result[3];
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

    public function calculateAverages(OutputInterface $o, $experimentName) {
        $runs = [];

        foreach (Finder::findDirectories("[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]")->from(APP_DIR."/../results/". $experiment->name) as $resultDir) {
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
                $this->experimentRepository->persistAndFlush($experiment, true);
                $runs[] = $experiment;
            } catch (\Exception $e) {
                //TODO - error handling into output here
                $o->writeln($e->getMessage());
            }
        }

        return $runs;
    }
}
