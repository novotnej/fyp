<?php
namespace App\Services;

use App\Repositories\UsersRepository;
use Nextras\Orm\Model\Model;

/**
 * Class OrmService
 * @package App\Services
 * @property-read UsersRepository $users
 */
class OrmService extends Model {
    public static $PG;

    public function setPG($pg) {
        static::$PG = $pg;
    }
}
