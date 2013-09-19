<?php

namespace Honeybee\Core\Job\Queue;

interface IQueueItem
{
    public function toArray();
}
