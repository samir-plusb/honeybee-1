<?php

namespace Honeybee\Core\Job\Queue;

interface IFifoQueue extends IQueue
{
    public function push(IQueueItem $item);

    public function shift();
}
