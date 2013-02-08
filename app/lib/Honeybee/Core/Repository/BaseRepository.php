<?php

namespace Honeybee\Core\Repository;

use Honeybee\Core\Finder\IFinder;
use Honeybee\Core\Storage\IStorage;
use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Dat0r\DocumentCollection;

abstract class BaseRepository implements IRepository
{
    private $module;

    private $finder;

    private $storage;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function initialize(IFinder $finder = NULL, IStorage $storage = NULL)
    {
        $this->finder = $finder;
        $this->storage = $storage;
    }

    public function getFinder()
    {
        return $this->finder;
    }

    public function getStorage()
    {
        return $this->storage;
    }
    
    public function getModule()
    {
        return $this->module;
    }
}
