<?php

namespace Honeybee\Core\Dat0r;

use Dat0r\Core\Runtime\Module\RootModule;
use Dat0r\Core\Runtime\Field\TextField;
use Dat0r\Core\Runtime\Field\IntegerField;
use Dat0r\Core\Runtime\Field\UuidField;
use Honeybee\Core\Workflow;
use Honeybee\Core\Storage\CouchDb\TreeStorage;

/**
 * @todo We might want to merge the module settings.xml options,
 * with the options that result from the dat0r definition??
 */
abstract class Module extends RootModule
{
    private $services;

    private $repositories;

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

    public function getService($context = 'default')
    {
        if (! is_array($this->services))
        {
            $this->services = array();
        }

        if (! isset($this->services[$context]))
        {
            $this->services[$context] = ModuleFactory::createService($this, $context);
        }

        return $this->services[$context];
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

    public function getWorkflowManager()
    {
        if (NULL === $this->workflowManager)
        {
            $this->workflowManager = new Workflow\Manager($this);
        }

        return $this->workflowManager;
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
                UuidField::create('uuid'),
                TextField::create('language', array('default_value' => 'DE_de')),
                IntegerField::create('version', array('default_value' => 1))
            )
        );
    }
}
