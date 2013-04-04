<?php

namespace Honeybee\Core\Import;

use Honeybee\Core\Config;

/*
$service = new Import\Service();
$report = $service->consume('test-consumer', array(), array('data' => array(
    array('title' => 'Foo title', 'teaser' => 'Bar teaser'),
    array('title' => 'Baz 2 title', 'teaser' => 'Baz 2 teaser', 'asdad' => 'asasdaf')
)));
*/
class Service
{
    protected $consumerConfig;

    protected $providerConfig;

    public function __construct()
    {
        // @todo Use Config\AgaviXmlConfig here
        $this->consumerConfig = include \AgaviConfigCache::checkConfig(
            \AgaviConfig::get('core.config_dir') . '/import.consumers.xml'
        );
        $this->consumerConfig = $this->consumerConfig['consumers'];
        // @todo Use Config\AgaviXmlConfig here
        $this->providerConfig = include \AgaviConfigCache::checkConfig(
            \AgaviConfig::get('core.config_dir') . '/import.providers.xml'
        );
        $this->providerConfig = $this->providerConfig['providers'];
    }

    public function consume($consumerName, $consumerParams = array(), $providerParams = array())
    {
        $consumer = $this->getConsumer($consumerName, $consumerParams);
        $consumerDef = $this->consumerConfig[$consumerName];
        $provider = $this->getProvider($consumerDef['provider'], $providerParams);
        
        return $consumer->consume($provider);
    }

    public function getConsumer($name, array $parameters = array())
    {
        $consumer = NULL;

        if (isset($this->consumerConfig[$name]))
        {
            $consumerDef = $this->consumerConfig[$name];
            $consumer = $this->createEntity($consumerDef, $parameters);
            
            $filters = new Filter\FilterList();
            foreach ($consumerDef['filters'] as $filterDef)
            {
                $filterConfig = new Config\ArrayConfig($filterDef['settings']);
                $filters[] = new $filterDef['class']($filterConfig, $filterDef['name']);
            }

            $consumer->setFilters($filters);
        }

        return $consumer;
    }

    public function getProvider($name, array $parameters = array())
    {
        $provider = NULL;

        if (isset($this->providerConfig[$name]))
        {
            $provider = $this->createEntity($this->providerConfig[$name], $parameters);
        }

        return $provider;
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
