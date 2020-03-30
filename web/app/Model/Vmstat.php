<?php
namespace App\Model;

use Nextras\Orm\Relationships\ManyHasOne;

/**
 * Class Vmstat
 * @package App\Model
 * @property int $idle {default 0}
 * @property int $system {default 0}
 * @property int $user {default 0}
 * @property int $free {default 0}
 * @property int $buff {default 0}
 * @property int $cache {default 0}
 * @property int $timestamp {default 0}
 * @property ManyHasOne|Experiment $experiment {m:1 Experiment::$vmstats}
 */
class Vmstat extends CommonModel {

    public function getterIdle($idle) {
        return 100 - ($this->system + $this->user);
    }
}