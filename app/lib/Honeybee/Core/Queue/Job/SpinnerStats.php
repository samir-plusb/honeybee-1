<?php

namespace Honeybee\Core\Queue\Job;

class SpinnerStats extends RunnableStats
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
