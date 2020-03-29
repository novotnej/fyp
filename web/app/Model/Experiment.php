<?php
namespace App\Model;

use Nextras\Orm\Relationships\OneHasMany;

/**
 * Class Experiment
 * @package App\Model
 * @property string $name
 * @property \DateTimeImmutable $time
 * @property-read \DateTimeImmutable $timeEnd {virtual}
 * @property int $iterations
 * @property int $sleep
 * @property int $threadCount
 * @property int $contentLength
 * @property OneHasMany|Thread[] $threads {1:m Thread::$experiment}
 * @property double $averageServerTime {virtual}
 * @property double $averageTotalTime {virtual}
 * @property double $averageNetworkLatency {virtual}
 */
class Experiment extends CommonModel {
    public function getterAverageTotalTime() {
        return rand();
    }

    public function getterAverageServerTime() {
        return rand();
    }

    public function getterAverageNetworkLatency() {
        return $this->averageTotalTime - $this->averageServerTime;
    }

    //find the last thread run and use its timestamp
    public function getterTimeEnd() {
        $latestRun = null;
        foreach ($this->threads as $thread) {
            foreach ($thread->runs as $run) {
                if ($latestRun == null) {
                    $latestRun = $run;
                }
                if ($latestRun->time < $run->time) {
                    $latestRun = $run;
                }
            }
        }

        return $latestRun->time;
    }
}