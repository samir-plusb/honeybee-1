<?php

namespace Honeybee\Core\Dat0r;

use \Honeybee\Core\Repository\IService;
use \Honeybee\Core\Finder\IFinder;
use \Honeybee\Core\Storage\IStorage;
use \Honeybee\Core\Repository\IRepository;

use \AgaviContext;
use \InvalidArgumentException;

// @todo maybe we should rename the create methods into get/fetch methods 
// and pool the instances inside the factory, so we can remove the getRepository and getService
// methods from the module.
class ModuleFactory
{
    public static function createRepository(Module $module)
    {
        $implementor = $module->getRepositoryImplementor();

        if (! class_exists($implementor))
        {
            throw new InvalidArgumentException(
                sprintf(
                    "Unable to load repository class %s for module %s.", 
                    $implementor, $module->getName()
                )
            );
        }

        $finder = self::createFinder($module);
        $storage = self::createStorage($module);
        
        $repository = new $implementor($module);

        if (! $repository instanceof IRepository)
        {
            throw new InvalidArgumentException(
                sprintf(
                    "The given repository %s for module %s must implement the IRepository interface.",
                    $implementor, $module->getName()
                )
            );
        }

        $repository->initialize($finder, $storage);

        return $repository;
    }

    public static function createService(Module $module)
    {
        $implementor = $module->getServiceImplementor();

        if (! class_exists($implementor))
        {
            throw new InvalidArgumentException(
                sprintf(
                    "Unable to load service class %s for module %s.", 
                    $implementor, $module->getName()
                )
            );
        }

        $service = new $implementor($module);

        if (! $service instanceof IService)
        {
            throw new InvalidArgumentException(
                sprintf(
                    "The given service %s for module %s must implement the IService interface.",
                    $implementor, $module->getName()
                )
            );
        }

        return $service;
    }

    protected static function createFinder(Module $module)
    {
        $implementor = $module->getFinderImplementor();

        if (! class_exists($implementor))
        {
            throw new InvalidArgumentException(
                sprintf(
                    "Unable to load finder class %s for module %s.", 
                    $implementor, $module->getName()
                )
            );
        }

        $context = AgaviContext::getInstance();
        $dbManager = $context->getDatabaseManager();
        $finder = new $implementor(
            $dbManager->getDatabase($module->getConnectionName('Read')),
            $module->getOption('prefix')
        );

        if (! $finder instanceof IFinder)
        {
            throw new InvalidArgumentException(
                sprintf(
                    "The given finder %s for module %s must implement the IFinder interface.",
                    $implementor, $module->getName()
                )
            );
        }

        return $finder;
    }

    protected static function createStorage(Module $module)
    {
        $implementor = $module->getStorageImplementor();

        if (! class_exists($implementor))
        {
            throw new InvalidArgumentException(
                sprintf(
                    "Unable to load storage class %s for module %s.", 
                    $implementor, $module->getName()
                )
            );
        }

        $context = AgaviContext::getInstance();
        $dbManager = $context->getDatabaseManager();
        $storage = new $implementor(
            $dbManager->getDatabase($module->getConnectionName('Write'))
        );

        if (! $storage instanceof IStorage)
        {
            throw new InvalidArgumentException(
                sprintf(
                    "The given storage %s for module %s must implement the IStorage interface.",
                    $implementor, $module->getName()
                )
            );
        }

        return $storage;
    }
}
