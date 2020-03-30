<?php
namespace App\Model;

use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * Class Thread
 * @package App\Model
 * @property string $name
 * @property OneHasMany|ThreadRun[] $runs {1:m ThreadRun::$thread}
 * @property ManyHasOne|Experiment $experiment {m:1 Experiment::$threads}
 */
class Thread extends CommonModel {

}