<?php

namespace Honeybee\Infrastructure\Event;

interface EventInterface
{
    public function getUuid();

    public function getTimestamp();

    public function getDateTime();

    public function getIsoDate();

    public function getMetaData();

    public function getType();
}
