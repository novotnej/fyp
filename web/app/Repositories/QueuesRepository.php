<?php
namespace App\Repositories;

use App\Model\Queue;

class QueuesRepository extends CommonRepository {

    static function getEntityClassNames() :array {
        return [Queue::class];
    }
}