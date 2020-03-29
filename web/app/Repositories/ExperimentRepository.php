<?php
namespace App\Repositories;

use App\Model\Experiment;

class ExperimentRepository extends CommonRepository {

    static function getEntityClassNames() :array {
        return [Experiment::class];
    }
}