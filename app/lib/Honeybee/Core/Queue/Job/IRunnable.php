<?php

namespace Honeybee\Core\Queue\Job;

interface IRunnable
{
    public function run(array $parameters = array());
}
