<?php
namespace App\Repositories;

use App\Model\ThreadRun;

class ThreadRunRepository extends CommonRepository {

    static function getEntityClassNames() :array {
        return [ThreadRun::class];
    }
}