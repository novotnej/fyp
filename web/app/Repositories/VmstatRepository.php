<?php
namespace App\Repositories;

use App\Model\Vmstat;

class VmstatRepository extends CommonRepository {

    static function getEntityClassNames() :array {
        return [Vmstat::class];
    }
}