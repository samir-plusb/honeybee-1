<?php

namespace Honeybee\Core\Import;

use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Service\IService;
use Honeybee\Core\Config;

class Service implements IService
{
    protected $module;

    protected $imports;

    protected $consumerConfig;

    public function __construct(Module $module)
    {
        $this->module = $module;
        $this->imports = array();

        $importConfig = new Config\AgaviXmlConfig(
            \AgaviConfig::get('core.modules_dir') . '/' . $module->getName() . '/config/imports.xml'
        );

        $this->consumerConfig = new Config\ArrayConfig($importConfig->get('consumers'));
    }

    public function consume($consumerName, $consumerParams = array(), $providerParams = array())
    {
        $consumer = $this->getConsumer($consumerName, $consumerParams);

        if (! $consumer)
        {
            throw new \InvalidArgumentException("Consumer '$consumerName' could not be resolved. Typo in argument or config?");
        }

        $consumerDef = $this->consumerConfig->get($consumerName);
        $provider = $this->createEntity($consumerDef['provider'], $providerParams);
        
        return $consumer->consume($provider);
    }

    public function getConsumer($name, array $parameters = array())
    {
        $consumer = NULL;

        if (isset($this->imports[$name]))
        {
            return $this->imports[$name];
        }

        if ($this->consumerConfig->has($name))
        {
            $consumerDef = $this->consumerConfig->get($name);
            $consumer = $this->createEntity($consumerDef, $parameters);
            
            $filters = new Filter\FilterList();
            foreach ($consumerDef['filters'] as $filterDef)
            {
                $filterConfig = new Config\ArrayConfig($filterDef['settings']);
                $filters[] = new $filterDef['class']($filterConfig, $filterDef['name']);
            }

            $consumer->setFilters($filters);
        }
        $this->imports[$name] = $consumer;

        return $consumer;
    }

    protected function createEntity(array $factoryInfo, array $parameters)
    {
        $class = $factoryInfo['class'];
        $name = $factoryInfo['name'];
        $description = $factoryInfo['description'];

        $config = new Config\ArrayConfig(
            isset($factoryInfo['settings']) ? $factoryInfo['settings'] : array()
        );

        $entity = new $class($config, $name, $description);
        $entity->initialize($parameters);

        return $entity;
    }
}
