<?php

namespace Honeybee\Infrastructure\Event;

use Trellis\Common\Object;
use DateTimeImmutable;
use DateTime;

abstract class Event extends Object implements EventInterface
{
    const DATE_ISO8601_WITH_MICROS = 'Y-m-d\TH:i:s.uP';

    protected $uuid;

    protected $iso_date;

    public function __construct(array $state = [])
    {
        $this->iso_date = DateTimeImmutable::createFromFormat('U.u', sprintf('%.6F', microtime(true)))
            ->format(self::DATE_ISO8601_WITH_MICROS);

        parent::__construct($state);

        $this->guardRequiredState();
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getTimestamp()
    {
        return $this->getDateTime()->format('U');
    }

    public function getDateTime()
    {
        return new DateTimeImmutable($this->iso_date);
    }

    public function getIsoDate()
    {
        return $this->iso_date;
    }

    protected function guardRequiredState()
    {
        assert($this->iso_date !== null, 'iso_date is set');
        assert($this->uuid !== null, 'uuid is set:' . get_class($this));
    }

    public function __toString()
    {
        return static::CLASS . '@' . $this->getUuid();
    }
}