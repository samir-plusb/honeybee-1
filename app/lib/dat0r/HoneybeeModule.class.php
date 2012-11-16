<?php

use Dat0r\Core\Runtime\Module\RootModule;
use Dat0r\Core\Runtime\Field\TextField;

/**
 * @todo We might want to merge the module settings.xml options,
 * with the options that result from the dat0r definition??
 */
abstract class HoneybeeModule extends RootModule
{
    private $repository;

    public function getRepository()
    {
        if (NULL === $this->repository)
        {
            $this->repository = HoneybeeModuleFactory::createRepository($this);
        }

        return $this->repository;
    }

    public function getConnectionName($type)
    {
        $supportedTypes = array('Read', 'Write');

        if (! in_array($type, $supportedTypes))
        {
            throw new InvalidArgumentException(
                "Unsupported connection type '$type' given. Supported are 'Read' and 'Write'."
            );
        }

        return sprintf('%s.%s', $this->getName(), $type);
    }

    public function getStorageImplementor()
    {
        $settingName = $this->getOption('prefix') . '.storage';
        return AgaviConfig::get($settingName, 'CouchDbStorage');
    }

    public function getFinderImplementor()
    {
        $settingName = $this->getOption('prefix') . '.finder';
        return AgaviConfig::get($settingName, 'ElasticSearchFinder');
    }

    /**
     * Returns the default fields that are initially added to a module upon creation.
     *
     * @return array A list of IField implemenations.
     */
    protected function getDefaultFields()
    {
        return array_merge(
            parent::getDefaultFields(),
            array(
                TextField::create('identifier'),
                TextField::create('revision')
            )
        );
    }
}
