<?php
namespace App\Console;

use App\Model\Experiment;
use App\Services\ResultService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MergeResultFilesCommand extends Command {
    const   ARG_EXPERIMENT  = "experiment";

    protected function configure() {
        $this->setName('app:mergeResultFiles')
            ->setDescription('Merges thread results files into a single file')
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
        $this->resultService->mergeResultFiles($output, $experiment);

        return 0;
    }
}
