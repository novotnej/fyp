<?php
namespace App\Console;

use App\Model\Experiment;
use App\Services\JavaResultService;
use App\Services\ResultService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateJavaCommand extends Command {
    const   ARG_EXPERIMENT  = "experiment",
            ARG_STAT_FILE   = "stat_file";

    protected function configure() {
        $this->setName('app:calculateJava')
            ->setDescription('Calculate average execution times from a java experiment')
            ->addArgument(self::ARG_EXPERIMENT, InputArgument::REQUIRED, 'Name of the experiment (directory within results)')
            ->addArgument(self::ARG_STAT_FILE, InputArgument::OPTIONAL, 'Name of the vmstat file', "vmstat.txt")
        ;
    }

    /** @var OutputInterface */
    protected $output;
    /** @var JavaResultService */
    protected $resultService;

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->resultService = $this->getHelper("container")->getByType("App\Services\JavaResultService");
        $this->output = $output;
        $experiment = $input->getArgument(self::ARG_EXPERIMENT);
        $statFile = $input->getArgument(self::ARG_STAT_FILE);
        /** @var Experiment $result */
        $results = $this->resultService->calculateAverages($output, $experiment, $statFile);
        $output->writeln("Experiment: " . $result->name);
        foreach ($results as $result) {
            $output->writeln("$result->contentLength;$result->averageServerTime");
            //$output->writeln("Start:". $result->time->format("H:i:s").";End:".$result->timeEnd->format("H:i:s"));

        }
        return 0;
    }
}
