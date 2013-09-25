<?php

namespace Honeybee\Core\Job\Queue\Runnable;

class SpinnerStats extends Stats
{
    protected $worker_stats = array();

    protected $executing_jobs = 0;

    public function onJobStarted()
    {
        $this->executing_jobs++;
    }

    public function onJobFinished()
    {
        $this->executing_jobs--;
    }

    public function getExecutingJobs()
    {
        return $this->executing_jobs;
    }

    public function getWorkerStats($pid = null)
    {
        if ($pid !== null && $pid > 0) {
            return isset($this->worker_stats[$pid]) ? $this->worker_stats[$pid] : null;
        }
        return $this->worker_stats;
    }

    public function setWorkerStats($worker_pid, array $stats)
    {
        $this->worker_stats[$worker_pid] = $stats;
    }

    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            array(
                'worker_stats' => $this->worker_stats,
                'executing_jobs' => $this->executing_jobs,
                'memory' => memory_get_usage(true)
            )
        );
    }
}
