<?php

namespace Honeybee\Core\Dat0r;

use Zend\Permissions\Acl;
use Dat0r\Core\Runtime\Document\Document as BaseDocument;
use Honeybee\Core\Workflow\IResource;
use Dat0r\Core\Runtime\Module\IModule;

abstract class Document extends BaseDocument implements IResource, Acl\Resource\ResourceInterface
{
    public function getWorkflowConfigPath()
    {
        $moduleDir = \AgaviConfig::get('core.modules_dir') 
            . DIRECTORY_SEPARATOR . $this->getModule()->getName();

        return $moduleDir . DIRECTORY_SEPARATOR . 'config' 
            . DIRECTORY_SEPARATOR . 'workflows.xml';
    }

    public function setIdentifier($identifier)
    {
        $this->setValue('identifier', $identifier);
    }

    public function getIdentifier()
    {
        return sprintf(
            '%s-%s-%s-%s',
            $this->getModule()->getOption('prefix'),
            $this->getValue('uuid'), 
            $this->getValue('language'), 
            $this->getValue('version')
        );
    }

    public function setRevision($revision)
    {
        $this->setValue('revision', $revision);
    }

    public function getRevision()
    {
        return $this->getValue('revision');
    }

    public function getResourceId()
    {
        return $this->getModule()->getOption('prefix');
    }

    protected function hydrate(array $values = array())
    {
        parent::hydrate($values);

        if (! $this->hasValue('uuid'))
        {
            $this->setValue('uuid', $this->getValue('uuid'));
        }
    }
}
