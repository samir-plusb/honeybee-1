<?php

namespace Honeybee\Infrastructure\Command;

use Trellis\Common\Object;
use Honeybee\Common\Util\StringToolkit;
use Rhumsaa\Uuid\Uuid;

abstract class Command extends Object implements CommandInterface
{
    protected $uuid;

    protected $meta_data;

    public function __construct(array $state = array())
    {
        $this->meta_data = [];
        $this->uuid = Uuid::uuid4()->toString();

        parent::__construct($state);

        $this->guardRequiredState();
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getMetaData()
    {
        return $this->meta_data;
    }

    public function __toString()
    {
        return static::CLASS . '@' . $this->uuid;
    }

    public static function getType()
    {
        $fqcn_parts = explode('\\', static::CLASS);
        if (count($fqcn_parts) < 4) {
            throw new RuntimeError(
                sprintf(
                    'A concrete command class must be made up of at least four namespace parts: ' .
                    '(vendor, package, type, event), in order to support auto-type generation.' .
                    ' The given command-class %s only has %d parts.',
                    static::CLASS,
                    count($fqcn_parts)
                )
            );
        }
        $vendor = strtolower(array_shift($fqcn_parts));
        $package = StringToolkit::asSnakeCase(array_shift($fqcn_parts));
        $type = StringToolkit::asSnakeCase(array_shift($fqcn_parts));
        $command = str_replace('_command', '', StringToolkit::asSnakeCase(array_pop($fqcn_parts)));

        return sprintf('%s.%s.%s.%s', $vendor, $package, $type, $command);
    }

    protected function guardRequiredState()
    {
        assert($this->uuid !== null, 'uuid is set');
        assert(is_array($this->meta_data), 'meta-data is an array');
    }
}
