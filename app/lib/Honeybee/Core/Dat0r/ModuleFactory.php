<?php

namespace Honeybee\Core\Dat0r;

use Honeybee\Core\Service\IService;
use Honeybee\Core\Finder\IFinder;
use Honeybee\Core\Storage\IStorage;
use Honeybee\Core\Repository\IRepository;

// @todo maybe we should rename the create methods into get/fetch methods 
// and pool the instances inside the factory, so we can remove the getRepository and getService
// methods from the module.
class ModuleFactory
{
    public static function createRepository(Module $module, $context = 'default')
    {
        $implementor = self::getRepositoryImplementor($module, $context);

        if (! class_exists($implementor))
        {
            throw new \InvalidArgumentException(
                sprintf(
                    "Unable to load repository class %s for module %s.", 
                    $implementor, $module->getName()
                )
            );
        }

        $finder = self::createFinder($module, $context);
        $storage = self::createStorage($module, $context);
        
        $repository = new $implementor($module);

        if (! $repository instanceof IRepository)
        {
            throw new \InvalidArgumentException(
                sprintf(
                    "The given repository %s for module %s must implement the IRepository interface.",
                    $implementor, $module->getName()
                )
            );
        }

        $repository->initialize($finder, $storage);

        return $repository;
    }

    public static function createService(Module $module, $context = 'default')
    {
        $implementor = self::getServiceImplementor($module, $context);

        if (! class_exists($implementor))
        {
            throw new \InvalidArgumentException(
                sprintf(
                    "Unable to load service class %s for module %s.", 
                    $implementor, $module->getName()
                )
            );
        }

        $service = new $implementor($module);

        if (! $service instanceof IService)
        {
            throw new \InvalidArgumentException(
                sprintf(
                    "The given service %s for module %s must implement the IService interface.",
                    $implementor, $module->getName()
                )
            );
        }

        return $service;
    }

    protected static function createFinder(Module $module, $context = 'default')
    {
        $implementor = self::getFinderImplementor($module, $context);

        if (! class_exists($implementor))
        {
            throw new \InvalidArgumentException(
                sprintf(
                    "Unable to load finder class %s for module %s.", 
                    $implementor, $module->getName()
                )
            );
        }

        $context = \AgaviContext::getInstance();
        $dbManager = $context->getDatabaseManager();
        $finder = new $implementor(
            $dbManager->getDatabase(self::getConnectionName($module, 'Read')),
            $module->getOption('prefix')
        );

        if (! $finder instanceof IFinder)
        {
            throw new \InvalidArgumentException(
                sprintf(
                    "The given finder %s for module %s must implement the IFinder interface.",
                    $implementor, $module->getName()
                )
            );
        }

        return $finder;
    }

    protected static function createStorage(Module $module, $context = 'default')
    {
        $implementor = self::getStorageImplementor($module, $context);

        if (! class_exists($implementor))
        {
            throw new \InvalidArgumentException(
                sprintf(
                    "Unable to load storage class %s for module %s.", 
                    $implementor, $module->getName()
                )
            );
        }

        $context = \AgaviContext::getInstance();
        $dbManager = $context->getDatabaseManager();
        $storage = new $implementor(
            $dbManager->getDatabase(self::getConnectionName($module, 'Write'))
        );

        if (! $storage instanceof IStorage)
        {
            throw new \InvalidArgumentException(
                sprintf(
                    "The given storage %s for module %s must implement the IStorage interface.",
                    $implementor, $module->getName()
                )
            );
        }

        return $storage;
    }

    public static function getServiceImplementor($module, $context)
    {
        switch ($context)
        {
            case 'tree':
                $default = 'Honeybee\\Core\\Service\\TreeService';
                break;
            case 'export':
                $default = 'Honeybee\\Core\\Export\\Service';
                break;
            default:
                $default = sprintf('%sService', $module->getName());
        }

        $settingName = $module->getOption('prefix') . '.service';

        return \AgaviConfig::get($settingName, $default);
    }

    public static function getRepositoryImplementor(Module $module, $context)
    {
        $default = 'Honeybee\\Core\\Repository\\DocumentRepository';
        if ('tree' === $context)
        {
            $default = 'Honeybee\\Core\\Repository\\TreeRepository';
        }

        $settingName = $module->getOption('prefix') . '.repository';

        return \AgaviConfig::get($settingName, $default);
    }

    public static function getStorageImplementor(Module $module, $context)
    {
        $default = 'Honeybee\\Core\\Storage\\CouchDb\\DocumentStorage';
        if ('tree' === $context)
        {
            $default = 'Honeybee\\Core\\Storage\\CouchDb\\TreeStorage';
        }

        $settingName = $module->getOption('prefix') . '.storage';

        return \AgaviConfig::get($settingName, $default);
    }

    public static function getFinderImplementor(Module $module, $context)
    {
        $default = 'Honeybee\\Core\\Finder\\ElasticSearch\\DocumentFinder';
        if ('tree' === $context)
        {
            $default = 'Honeybee\\Core\\Finder\\ElasticSearch\\TreeFinder';
        }

        $settingName = $module->getOption('prefix') . '.finder';
        
        return \AgaviConfig::get($settingName, $default);
    }

    public static function getConnectionName(Module $module, $accessType = 'Read')
    {
        $supportedTypes = array('Read', 'Write');

        if (! in_array($accessType, $supportedTypes))
        {
            throw new \InvalidArgumentException(
                "Unsupported connection type '$type' given. Supported are 'Read' and 'Write'."
            );
        }

        return sprintf('%s.%s', $module->getName(), $accessType);
    }
}
