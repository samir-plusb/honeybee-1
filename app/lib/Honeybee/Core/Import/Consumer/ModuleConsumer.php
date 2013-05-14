<?php

namespace Honeybee\Core\Import\Consumer;

use Honeybee\Core\Config;
use Honeybee\Core\Import\Provider;
use Honeybee\Core\Import\Filter;
use Dat0r\Core\Runtime\Module;

/**
 * The ModuleConsumer class is a concrete implementation of the BaseConsumer base class.
 * It's task is to create Documents from the data it pulls from a given provider.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class ModuleConsumer extends BaseConsumer
{
    /**
     * Holds a reference to the module we are creating documents for.
     *
     * @var         Module
     */
    private $module;

    /**
     * Initialize the provider with extrinsic parameters.
     *
     * @param       array $parameters
     */
    public function initialize(array $parameters = array())
    {
        parent::initialize($parameters);

        $moduleClass = $this->getConfig()->get('module');
        $this->module = $moduleClass::getInstance();
    }

    /**
     * Get the given data into the configured honeybee module.
     *
     * @param       array $data
     */
    protected function processData(array $data)
    {
        $updateField = $this->getConfig()->get('update_field');
        $document = NULL;

        if (!empty($updateField) && ($updateValue = Filter\RemapFilter::getArrayValue($data, $updateField)))
        {
            $parsedPath = Filter\RemapFilter::getPartsFromPath($updateField);
            $updateFilterField = implode('.', $parsedPath['parts']);

            $searchSpec = array('filter' => array($updateFilterField => $updateValue));
            $result = $this->module->getService()->find($searchSpec, 0, 1);

            if (1 < $result['totalCount'])
            {
                // wtf, multiple update candidates found ...
                error_log(__METHOD__ . " - Skipping import dataset due to multiple update candidates.");
            }
            else if (0 < $result['totalCount'])
            {
                $document = $result['documents'][0];
                $document->setValues($data);
            }
            else
            {
                $document = $this->module->createDocument($data);
            }
        }
        else
        {
            // really import this stuff, if we couldn't find an update value (origin info)?
            $document = $this->module->createDocument($data);
        }

        if ($document)
        {
            $this->module->getService()->save($document);
        }
    }
}
