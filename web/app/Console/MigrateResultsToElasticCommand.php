<?php
namespace App\Console;

use App\Services\ElasticService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateResultsToElasticCommand extends Command {

    protected function configure() {
        $this->setName('app:migrateResultsToElastic')
            ->setDescription('Indexes experiment results into elasticsearch');
    }

    /** @var OutputInterface */
    protected $output;
    /** @var ElasticService */
    protected $elasticService;

    protected $counter = 0;

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->elasticService = $this->getHelper("container")->getByType("App\Services\ElasticService");
        $this->output = $output;
        $this->elasticService->migrateResultsToElastic($output);
        return 0;
    }
}
