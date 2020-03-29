<?php
namespace App\Model;

use Nextras\Orm\Relationships\ManyHasOne;

/**
 * Class ThreadRun
 * @package App\Model
 * @property int $start
 * @property int $end
 * @property \DateTimeImmutable $time
 * @property ManyHasOne|Thread $thread {m:1 Thread::$runs}
 * @property int $localDuration
 * @property int $serverDuration
 */
class ThreadRun extends CommonModel {

}