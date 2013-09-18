<?php

namespace Honeybee\Core\Dat0r;

use Dat0r\Core\Module\RootModule;
use Dat0r\Core\Field\TextField;
use Dat0r\Core\Field\IntegerField;
use Dat0r\Core\Field\UuidField;
use Honeybee\Core\Workflow;
use Zend\Permissions\Acl;

/**
 * @todo We might want to merge the module settings.xml options,
 * with the options that result from the dat0r definition??
 */
abstract class Module extends RootModule implements Acl\Resource\ResourceInterface
{
    private $services;

    private $repositories;

    private $workflowManager;

    public function createDocument(array $data = array())
    {
        $document = parent::createDocument($data);
        $workflowTicket = $document->getWorkflowTicket()->first();
        if (!$workflowTicket || !($workflowName = $workflowTicket->getWorkflowName()))
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
        return true === $this->getOption('act_as_tree');
    }

    public function getResourceId()
    {
        return $this->getOption('prefix');
    }

    public function getIdentifierField()
    {
        return $this->getField('identifier');
    }

    public function getReferencingFieldIndices()
    {
        $index_fields = array();

        $module_service = new ModuleService();
        foreach ($module_service->getModules() as $module) {
            $reference_fields = $module->getFields(
                array(),
                array('Dat0r\Core\Field\ReferenceField')
            );
            foreach ($reference_fields as $reference_field) {
                foreach ($reference_field->getOption('references') as $reference_options) {
                    if (isset($reference_options['index_fields']) && $reference_options['module'] === '\\' . get_class($this)) {
                        $index_fields[$module->getName()] = array(
                            'reference_field' => $reference_field,
                            'reference_module' => $module,
                            'index_fields' => $reference_options['index_fields']
                        );
                    }
                }
            }
        }

        return $index_fields;
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
                'identifier' => TextField::create('identifier'),
                'revision' => TextField::create('revision'),
                'uuid' => UuidField::create('uuid'),
                'shortId' => IntegerField::create('shortId'),
                'slug' => TextField::create('slug'),
                'language' => TextField::create('language', array('default_value' => 'de_DE')),
                'version' => IntegerField::create('version', array('default_value' => 1))
            )
        );
    }
}
