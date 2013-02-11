<?php

namespace Honeybee\Core\Service;

use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Dat0r\Tree;
use Honeybee\Core\Finder\ElasticSearch\ListQueryBuilder;
use Honeybee\Core\Finder\ElasticSearch\SuggestQueryBuilder;
use Elastica;

use ListConfig;
use IListState;

class TreeService implements IService
{
    private $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function save(Tree\ITree $tree)
    {
        $this->verifyModuleTreeAccess();

        $repository = $this->module->getRepository('tree');
        $repository->write($tree);
    }

    public function get($treeName = 'tree-default')
    {
        $this->verifyModuleTreeAccess();

        $repository = $this->module->getRepository('tree');
        $tree = $repository->read($treeName);

        return $tree ? $tree : $this->create($treeName);
    }

    public function delete(Tree\ITree $tree, $markOnly = TRUE)
    {
        $this->verifyModuleTreeAccess();

        $repository = $this->module->getRepository('tree');
        $repository->delete($tree);
    }

    public function create($treeName)
    {
        $this->verifyModuleTreeAccess();        

        return new Tree\Tree(
            $this->module, 
            array('identifier' => $treeName)
        );
    }

    protected function verifyModuleTreeAccess()
    {
        if (! $this->module->isActingAsTree())
        {
            throw new \Exception(sprintf(
                "The module %s is not acting as a tree. Please make sure you have apllied the acts_as_tree option.",
                $this->module->getName()
            ));
        }
    }
}
