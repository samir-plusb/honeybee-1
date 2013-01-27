<?php

namespace Honeybee\Core\Dat0r;

use Dat0r\Core\Runtime\Module\RootModule;
use Dat0r\Core\Runtime\Field\TextField;
use Honeybee\Core\Workflow;

/**
 * @todo We might want to merge the module settings.xml options,
 * with the options that result from the dat0r definition??
 */
abstract class Module extends RootModule
{
    private $repository;

    private $service;

    private $workflowManager;

    public function createDocument(array $data = array())
    {
        if (! empty($data))
        {
            $references = RelationManager::loadReferences($this, $data);
            $data = array_merge($data, $references);
        }
        
        $document = parent::createDocument($data);

        if (! ($ticket = $document->getWorkflowTicket()))
        {
            $this->getWorkflowManager()->initWorkflowFor($document);
        }

        return $document;
    }

    public function getService()
    {
        if (NULL === $this->service)
        {
            $this->service = ModuleFactory::createService($this);
        }

        return $this->service;
    }

    public function getWorkflowManager()
    {
        if (NULL === $this->workflowManager)
        {
            $this->workflowManager = new Workflow\Manager($this);
        }

        return $this->workflowManager;
    }

    public function getRepository()
    {
        if (NULL === $this->repository)
        {
            $this->repository = ModuleFactory::createRepository($this);
        }

        return $this->repository;
    }

    public function getConnectionName($type)
    {
        $supportedTypes = array('Read', 'Write');

        if (! in_array($type, $supportedTypes))
        {
            throw new \InvalidArgumentException(
                "Unsupported connection type '$type' given. Supported are 'Read' and 'Write'."
            );
        }

        return sprintf('%s.%s', $this->getName(), $type);
    }

    public function getServiceImplementor()
    {
        $defaultService = sprintf('%sService', $this->getName());
        $settingName = $this->getOption('prefix') . '.service';

        return \AgaviConfig::get($settingName, $defaultService);
    }

    public function getRepositoryImplementor()
    {
        $settingName = $this->getOption('prefix') . '.repository';

        return \AgaviConfig::get($settingName, '\\Honeybee\\Core\\Repository\\GenericRepository');
    }

    public function getStorageImplementor()
    {
        $settingName = $this->getOption('prefix') . '.storage';

        return \AgaviConfig::get($settingName, '\\Honeybee\\Core\\Storage\\CouchDb\\Storage');
    }

    public function getFinderImplementor()
    {
        $settingName = $this->getOption('prefix') . '.finder';
        
        return \AgaviConfig::get($settingName, '\\Honeybee\\Core\\Finder\\ElasticSearch\\Finder');
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
                TextField::create('revision'),
            )
        );
    }
}
