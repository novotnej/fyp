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
 * @property-read double $averageServerTime {virtual}
 * @property-read double $averageTotalTime {virtual}
 * @property-read double $averageNetworkLatency {virtual}
 * @property-read Vmstat $averageVmStat {virtual}
 */
class Experiment extends CommonModel {

    /** @var Vmstat */
    private $avgVmStat;

    public function getterAverageVmStat() {
        if (!$this->avgVmStat) {
            $this->avgVmStat = new Vmstat();

            foreach ($this->vmstats as $vmstat) {
                $this->avgVmStat->free += $vmstat->free;
                $this->avgVmStat->buff += $vmstat->buff;
                $this->avgVmStat->cache += $vmstat->cache;
                $this->avgVmStat->system += $vmstat->system;
                $this->avgVmStat->idle += $vmstat->idle;
                $this->avgVmStat->user += $vmstat->user;
            }
            $this->avgVmStat->free = (int) $this->avgVmStat->free / count($this->vmstats);
            $this->avgVmStat->buff = (int) $this->avgVmStat->buff / count($this->vmstats);
            $this->avgVmStat->cache = (int) $this->avgVmStat->cache / count($this->vmstats);
            $this->avgVmStat->system = (int) $this->avgVmStat->system / count($this->vmstats);
            $this->avgVmStat->idle = (int) $this->avgVmStat->idle / count($this->vmstats);
            $this->avgVmStat->user = (int) $this->avgVmStat->user / count($this->vmstats);
        }

        return $this->avgVmStat;
    }


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