<?php
namespace App\Repositories;

use App\Model\Device;

class DevicesRepository extends CommonRepository {

    static function getEntityClassNames() :array {
        return [Device::class];
    }
}