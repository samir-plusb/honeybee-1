<?php

namespace Honeybee\Core\Job\Queue\Spinner;

use Honeybee\Core\Job\Queue\Runnable\Stats as BaseStats;

class Stats extends BaseStats
{
    protected $worker_stats = array();

    public function setWorkerStats($worker_pid, array $stats)
    {
        $this->worker_stats[$worker_pid] = $stats;
    }

    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            array('worker_stats' => $this->worker_stats)
        );
    }
}
