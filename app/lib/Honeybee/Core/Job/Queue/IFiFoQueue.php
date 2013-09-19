<?php

namespace Honeybee\Core\Job\Queue;

interface IFiFoQueue extends IQueue
{
    public function push(IQueueItem $item);

    public function shift();
}
