<?php

namespace Honeybee\Core\Service;

use Honeybee\Core\Dat0r\ModuleFactory;
use Honeybee\Core\Dat0r\Module;
use Honeybee\Agavi\Database\CouchDb\ClientException;

class ShortIdService implements IShortIdService, IService
{
    const DOC_ID = 'short_id_service-sequence';

    protected $module;

    protected $client;

    public function __construct(Module $module)
    {
        $this->module = $module;

        // @todo reuse storage here; needs storage refactoring, hence only exchange arrays with the storage
        // and not documents.
        $this->client = \AgaviContext::getInstance()->getDatabaseConnection(
            ModuleFactory::getConnectionName($this->module, 'Write')
        );
    }

    public function get($prefix)
    {
        $sequenceDoc = $this->loadSequence();

        if (NULL === $sequenceDoc)
        {
            $sequenceDoc = $this->initSequence($prefix);
        }

        if (!isset($sequenceDoc[$prefix]))
        {
            $sequenceDoc[$prefix] = 0;
        }

        $sequenceDoc[$prefix] += 1;
        $this->client->storeDoc(NULL, $sequenceDoc);

        return $sequenceDoc[$prefix];
    }

    protected function loadSequence()
    {
        $sequenceDoc = NULL;

        try
        {
            $sequenceDoc = $this->client->getDoc(NULL, self::DOC_ID); 
        }
        catch(ClientException $e)
        {
            if (preg_match('~(\(404\))~', $e->getMessage()))
            {
                $sequenceDoc = NULL;
            }
            else
            {
                throw $e;
            }
        }

        return $sequenceDoc;
    }

    protected function initSequence($prefix = NULL)
    {
        $sequenceDoc = array('_id' => self::DOC_ID);

        if ($prefix)
        {
            // @todo query view and 
            $sequenceDoc[$prefix] = 0;
        }

        $result = $this->client->storeDoc(NULL, $sequenceDoc);
        $sequenceDoc['_rev'] = $result['rev'];

        return $sequenceDoc;
    }
}
