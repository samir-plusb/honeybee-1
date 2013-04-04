<?php

namespace Honeybee\Core\Service;

use Honeybee\Core\Dat0r\Module;

class ShortIdService implements IShortIdService, IService
{
    const SEQUENCE_KEY = 'short_id_service-sequence';

    protected $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function get($prefix)
    {
        $storage = $this->module->getRepository()->getStorage();
        $sequenceDoc = $storage->read(self::SEQUENCE_KEY);

        if (NULL === $sequenceDoc)
        {
            $sequenceDoc = array('identifier' => self::SEQUENCE_KEY);
        }

        if (! isset($sequenceDoc[$prefix]))
        {
            $sequenceDoc[$prefix] = 0;
        }

        $sequenceDoc[$prefix]++;
        $sequenceDoc['type'] = get_class($this);

        $storage->write($sequenceDoc);

        return $sequenceDoc[$prefix];
    }
}
