<?php

namespace Honeybee\Core\Job\Queue\Runnable;

interface IRunnable
{
    public function run(array $parameters = array());
}
