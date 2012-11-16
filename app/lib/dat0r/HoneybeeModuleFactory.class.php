<?php

class HoneybeeModuleFactory
{
    public static function createRepository(HoneybeeModule $module)
    {
        $finder = self::createFinder($module);
        $storage = self::createStorage($module);
        
        $repository = new GenericRepository($module);
        $repository->initialize($finder, $storage);

        return $repository;
    }

    protected static function createFinder(HoneybeeModule $module)
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
                "The given finder %s for module %s must implement the IFinder interface.",
                $implementor, $module->getName()
            );
        }

        return $finder;
    }

    protected static function createStorage(HoneybeeModule $module)
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
                "The given storage %s for module %s must implement the IStorage interface.",
                $implementor, $module->getName()
            );
        }

        return $storage;
    }
}
