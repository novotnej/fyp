<?php
namespace App\Model;

use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * Class Queue
 * @package App\Model
 * @property string $name
 * @property OneHasMany|ThreadRun[] $runs {1:m ThreadRun::$thread}
 * @property ManyHasOne|Experiment $experiment {m:1 Experiment::$threads}
 * @property double $averageNetworkLatency {virtual}
 */
class Thread extends CommonModel {
    public function getterAverageNetworkLatency() {
        return rand();
    }
}