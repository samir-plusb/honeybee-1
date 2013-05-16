<?php

namespace Honeybee\Core\Import\Provider;

use \Dat0r\Core\Runtime\Sham\DataGenerator;

/**
 * The FakeProvider class generates fake document data.
 *
 * @author Steffen Gransow <graste@mivesto.de>
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 */
class FakeProvider extends BaseProvider
{
    /**
     * Settings key to set the number of entries to create.
     */
    const OPTION_LIMIT = 'limit';

    /**
     * Settings key to set the locale to use when generating fake data.
     */
    const OPTION_LOCALE = 'locale';

    /**
     * Settings key to set the classname of the module to use for fake data generation.
     */
    const OPTION_MODULE = 'module';

    /**
     * Settings key to use to exclude certain fieldnames of the module from the resulting array of values.
     */
    const OPTION_EXCLUDED_FIELDS = 'excluded_fields';

    /**
     * @var int maximum number of entries to generate
     */
    protected $maxNumberOfEntries = 50;

    /**
     * @var int number of entries already generated
     */
    protected $numberOfEntries = 0;

    /**
     * Initialize the provider with extrinsic parameters.
     *
     * @param array $parameters
     */
    public function initialize(array $parameters = array())
    {
        parent::initialize($parameters);

        $moduleName = $this->getConfig()->get(self::OPTION_MODULE);
        $this->module = $moduleName::getInstance();
        $this->numberOfEntries = 0;
        $this->maxNumberOfEntries = $this->getConfig()->get(self::OPTION_LIMIT, 50);
    }

    /**
     * @return boolean true if generation is not yet finished. False otherwise.
     */
    protected function forwardCursor()
    {
        $this->numberOfEntries++;
        return $this->numberOfEntries < $this->maxNumberOfEntries;
    }

    /**
     * @return array with fake data for the configured module on each call
     */
    protected function fetchData()
    {
        $default_fields_to_exclude = $this->module->getDefaultFieldnames();
        $default_fields_to_exclude[] = 'workflowTicket';
        $module_fields_to_exclude = $this->getConfig()->get(self::OPTION_EXCLUDED_FIELDS, array());
        $fields_to_exclude = array_merge($default_fields_to_exclude, $module_fields_to_exclude);

        $data = DataGenerator::createDataFor($this->module, array(
            DataGenerator::OPTION_LOCALE => $this->getConfig()->get(self::OPTION_LOCALE, 'de_DE'),
            /*
             * DataGenerator::OPTION_FIELD_VALUES => array(
             *     'language' => $this->getConfig()->get(self::OPTION_LOCALE, 'de_DE')
             * ),
             */
            DataGenerator::OPTION_EXCLUDED_FIELDS => $fields_to_exclude
        ));

        return $data;
    }

    /**
     * @return string current origin of data
     */
    protected function getCurrentOrigin()
    {
        return 'Fake entry ' . $this->numberOfEntries . '/' . $this->maxNumberOfEntries;
    }
}
