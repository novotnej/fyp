<?php
namespace App\Repositories;

use App\Model\Thread;

class ThreadRepository extends CommonRepository {

    static function getEntityClassNames() :array {
        return [Thread::class];
    }
}