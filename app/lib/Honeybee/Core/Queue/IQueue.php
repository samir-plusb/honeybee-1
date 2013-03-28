<?php

namespace Honeybee\Core\Queue;

interface IQueue
{
    public function getName();

    public function push(IQueueItem $item);

    public function openNext();

    public function closeCurrent();

    public function abortCurrent();
}
