<?php

namespace Honeybee\Core\Queue\Job;

class WorkerStats extends RunnableStats
{
    protected $started_jobs = 0;

    protected $successful_jobs = 0;

    protected $failed_jobs = 0;

    protected $fatal_jobs = 0;

    public function onJobStarted(IJob $job)
    {
        $this->started_jobs++;
    }

    public function onJobSuccess(IJob $job)
    {
        $this->successful_jobs++;
    }

    public function onJobError(IJob $job)
    {
        $this->failed_jobs++;
    }

    public function onJobFatal(IJob $job)
    {
        $this->fatal_jobs++;
    }

    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            array(
                'started_jobs' => $this->started_jobs,
                'successful_jobs' => $this->successful_jobs,
                'failed_jobs' => $this->failed_jobs,
                'fatal_jobs' => $this->fatal_jobs
            )
        );
    }
}
