<?php

namespace Honeybee\Core\Job\Queue\Runnable;

class SpinnerStats extends Stats
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
