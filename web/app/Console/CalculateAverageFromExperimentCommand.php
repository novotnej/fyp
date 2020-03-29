<?php
namespace App\Console;

use App\Model\Experiment;
use App\Services\ResultService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateAverageFromExperimentCommand extends Command {
    const ARG_EXPERIMENT = "experiment";

    protected function configure() {
        $this->setName('app:calculateAverageFromExperiment')
            ->setDescription('Calculate average execution times from an experiment')
            ->addArgument(self::ARG_EXPERIMENT, InputArgument::REQUIRED, 'Name of the experiment (directory within results)')
        ;
    }

    /** @var OutputInterface */
    protected $output;
    /** @var ResultService */
    protected $resultService;

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->resultService = $this->getHelper("container")->getByType("App\Services\ResultService");
        $this->output = $output;
        $experiment = $input->getArgument(self::ARG_EXPERIMENT);
        /** @var Experiment $result */
        $results = $this->resultService->calculateAverages($output, $experiment);
        $output->writeln("Experiment: " . $result->name);
        foreach ($results as $result) {
            $output->writeln("Threads:$result->threadCount;Iterations:$result->iterations;Sleep:$result->sleep;ContentLength:$result->contentLength;AVGSRV:$result->averageServerTime;AVGTOT:$result->averageTotalTime;AVGLAT:$result->averageNetworkLatency");
            $output->writeln("Start:". $result->time->format("H:i:s").";End:".$result->timeEnd->format("H:i:s"));

        }
        return 0;
    }
}
