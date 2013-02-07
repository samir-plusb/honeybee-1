<?php

namespace Honeybee\Core\Dat0r;

use Dat0r\Core\Runtime\Module\RootModule;
use Dat0r\Core\Runtime\Field\TextField;
use Honeybee\Core\Workflow;
use Honeybee\Core\Storage\CouchDb\TreeStorage;

/**
 * @todo We might want to merge the module settings.xml options,
 * with the options that result from the dat0r definition??
 */
abstract class Module extends RootModule
{
    private $repositories;

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

    public function getRepository($context = 'default')
    {
        if (! is_array($this->repositories))
        {
            $this->repositories = array();
        }

        if (! isset($this->repositories[$context]))
        {
            $this->repositories[$context] = ModuleFactory::createRepository($this, $context);
        }

        return $this->repositories[$context];
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

    public function isActingAsTree()
    {
        return 'yes' === $this->getOption('act_as_tree');
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
