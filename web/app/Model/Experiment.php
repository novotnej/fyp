<?php
namespace App\Model;

use Nextras\Orm\Relationships\OneHasMany;

/**
 * Class Experiment
 * time values (except timestamps) are in microseconds (usec)
 * @package App\Model
 * @property string $name
 * @property \DateTimeImmutable $time
 * @property-read \DateTimeImmutable $timeEnd {virtual}
 * @property int $iterations
 * @property int $sleep
 * @property int $threadCount
 * @property int $contentLength
 * @property OneHasMany|Thread[] $threads {1:m Thread::$experiment}
 * @property OneHasMany|Vmstat[] $vmstats {1:m Vmstat::$experiment}
 * @property double $averageServerTime {virtual}
 * @property double $averageTotalTime {virtual}
 * @property double $averageNetworkLatency {virtual}
 */
class Experiment extends CommonModel {

    public function getterAverageTotalTime() {
        $runCount = 0;
        $time = 0;
        foreach ($this->threads as $thread) {
            foreach ($thread->runs as $run) {
                $runCount++;
                $time+=$run->localDuration;
            }
        }
        return $time/$runCount;
    }

    public function getterAverageServerTime() {
        $runCount = 0;
        $time = 0;
        foreach ($this->threads as $thread) {
            foreach ($thread->runs as $run) {
                $runCount++;
                $time+=$run->serverDuration;
            }
        }
        return $time/$runCount;
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