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

    public function getShortIdentifier()
    {
        $type = $this->getModule()->getOption('prefix');
        
        return sprintf('%s-%s', $type, $this->getShortId());
    }

    public function setRevision($revision)
    {
        $this->setValue('revision', $revision);
    }

    public function getRevision()
    {
        return $this->getValue('revision');
    }

    public function setShortId($shortId)
    {
        $this->setValue('shortId', $shortId);
    }

    public function getShortId()
    {
        return $this->getValue('shortId');
    }

    public function setSlug($slug)
    {
        $this->setValue('slug', $slug);
    }

    public function getSlug()
    {
        return $this->getValue('slug');
    }

    public function getResourceId()
    {
        return $this->getModule()->getOption('prefix');
    }

    public function onBeforeWrite()
    {
        $shortId = $this->getShortId();

        if (!$this->hasValue('shortId') || empty($shortId))
        {
            $shortIdService = $this->getModule()->getService('short-id');

            $this->setShortId(
                $shortIdService->get($this->getModule()->getOption('prefix'))
            );
        }

        $slug = $this->getSlug();
        if (empty($slug))
        {
            $this->setSlug($this->buildSlug());
        }
    }

    protected function hydrate(array $values = array())
    {
        parent::hydrate($values);

        if (!$this->hasValue('uuid'))
        {
            $this->setValue('uuid', $this->getValue('uuid'));
        }
    }

    protected function buildSlug()
    {
        $matches = array();
        $slugPattern = $this->getModule()->getOption('slugPattern');
        preg_match_all('~(\{\w+\})*~is', $slugPattern, $matches, PREG_SET_ORDER);

        $search = array();
        $replace = array();

        foreach ($matches as $match)
        {
            if(!empty($match[0]))
            {
                $fieldname = str_replace(array('{', '}'), '', $match[0]);
                $slugFragmentValue = $this->getValue($fieldname);

                if (! is_scalar($slugFragmentValue) && ! is_callable(array($slugFragmentValue, 'toString')))
                {
                    throw new \InvalidArgumentException(
                        "Non-scalar field value for field $fieldname encountered while trying to build slug."
                    );
                }

                $search[] = $match[0];
                $replace[] = $slugFragmentValue;
            }
        }

        return $this->slugify(
            str_replace($search, $replace, $slugPattern)
        );
    }

    protected function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        // trim
        $text = trim($text, '-');
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // lowercase
        $text = strtolower($text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text))
        {
            return 'n-a';
        }

        return $text;
    }
}
