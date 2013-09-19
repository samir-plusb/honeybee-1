<?php

namespace Honeybee\Core\Job\Queue;

interface IQueue
{
    public function getName();

    public function hasItems();
}
