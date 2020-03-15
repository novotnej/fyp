<?php
namespace App\Services;


use Elastica\Document;
use Elastica\Index;
use Elastica\Type;
use Elastica\Type\Mapping;
use Kdyby\ElasticSearch\Client;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use SplFileInfo;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticService extends CommonService {

    /** @var Client */
    protected $elastic;
    /** @var Index  */
    protected $webExperimentIndex;
    /** @var Type */
    protected $experimentType;
    /** @var OutputInterface */
    protected $output;

    /**
     * ElasticService constructor.
     * @param Client $elastic
     */
    public function __construct(Client $elastic) {
        $this->elastic = $elastic;
        $this->webExperimentIndex = $this->elastic->getIndex("web_experiment");
        $this->experimentType = $this->webExperimentIndex->getType("experiments");
    }

    public function createExperimentType() {
        $mapping = new Mapping();
        $mapping->setType($this->experimentType);
        $mapping->setProperties([
            "experiment_id" => [
                "type" => "integer"
            ],
            "config" => [
                "type" => "object",
                "properties" => [
                    "timestamp" => ["type" => "integer"],
                    "time" => ["type" => "datetime"],
                    "iterations" => ["type" => "integer"],
                    "sleep" => ["type" => "integer"],
                    "threads" => ["type" => "integer"]
                ]
            ],
            "threads" => [
                "type" => "nested",
                "properties" => [
                    "thread_id" => ["type" => "keyword"],
                    "start_ts"=> ["type" => "integer"],
                    "end_ts" => ["type" => "integer"],
                    "server_duration" => ["type" => "integer"],
                    "client_duration" => ["type" => "integer"]
                ]
            ]
        ]);
        $mapping->send();
        return $this->experimentType;
    }

    protected function getResultDocument(SplFileInfo $resultFile) {
        $resultDocument = [];
        $fh = fopen($resultFile->getRealPath(), "r");
        while(!feof($fh)) {
            $line = fgets($fh);
            $result  = explode("|", $line);
            $parsedResult = [
                "thread_id" => $result[0],
                "start_ts" => (int)$result[1],
                "end_ts" => (int)$result[2],
                "server_duration" => (int)$result[3],
                "client_duration" => (int)str_replace(PHP_EOL, "", $result[4]),
            ];
            if ($parsedResult["thread_id"] != "") {
                $resultDocument[] = $parsedResult;
            }
            $this->output->writeln($line);
        }
        fclose($fh);
        return $resultDocument;
    }

    public function getUploadDocument($config, SplFileInfo $resultDir) {
        $resultKey = $resultDir->getFilename();
        $doc = [
            "experiment_id" => (int) $resultKey,
            "config" => $config,
            "threads" => []
        ];

        foreach (Finder::findFiles("$resultKey-*.txt")->from($resultDir->getRealPath()) as $resultFile) {
            /** @var SplFileInfo $resultFile  */
            //$this->output->writeln(" - ".$resultFile->getFilename());
            $doc["threads"][] = $this->getResultDocument($resultFile);
        }

        $this->output->writeln(json_encode($doc));
        return $doc;
    }

    public function migrateResultsToElastic(OutputInterface $output) {
        $this->output = $output;
        //[0-9]{10} does not work here for some reason
        foreach (Finder::findDirectories("[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]")->from(APP_DIR."/../results") as $resultDir) {
            /** @var SplFileInfo $resultDir  */
            $output->writeln($resultDir->getFilename());
            $configFileName = $resultDir->getRealPath()."/config.json";
            try {
                $configFile = FileSystem::read($configFileName);
                $config = @json_decode($configFile, true);
                if (!$config) {
                    throw new \Exception("Malformatted config file");
                }
                $dt = new \DateTime();
                $dt->setTimestamp($config["timestamp"]);
                $config["time"] = $dt->format("Y-m-d H:i:s");

                $doc = $this->getUploadDocument($config, $resultDir);
                $elasticDoc = new Document($resultDir->getFilename(), $doc);
                $this->experimentType->addDocument($elasticDoc);

            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
            }
        }
        $this->experimentType->getIndex()->refresh();
    }
}
