<?php
namespace App\Model;

use Nextras\Orm\Relationships\ManyHasOne;

/**
 * Class Vmstat
 * @package App\Model
 * @property int $idle
 * @property int $system
 * @property int $user
 * @property int $free
 * @property int $buff
 * @property int $cache
 * @property int $timestamp
 * @property ManyHasOne|Experiment $experiment {m:1 Experiment::$vmstats}
 */
class Vmstat extends CommonModel {

}