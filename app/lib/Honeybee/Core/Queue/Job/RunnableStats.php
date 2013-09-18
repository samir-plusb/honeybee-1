<?php

namespace Honeybee\Core\Queue\Job;

class RunnableStats
{
    protected $start_time;

    protected $shutdown_time;

    public function onRunnableStarted()
    {
        $this->start_time = new \DateTime();
    }

    public function onRunnableStopped()
    {
        $this->shutdown_time = new \DateTime();
    }

    public function toArray()
    {
        $start_time = '';
        if ($this->start_time instanceof \DateTime) {
            $start_time = $this->start_time->format(\DateTime::ISO8601);
        }

        $shutdown_time = '';
        if ($this->shutdown_time instanceof \DateTime) {
            $shutdown_time = $this->shutdown_time->format(\DateTime::ISO8601);
        }

        return array('start_time' => $start_time, 'shutdown_time' => $shutdown_time);
    }
}
