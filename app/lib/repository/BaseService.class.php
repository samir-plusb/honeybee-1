<?php

abstract class BaseService implements IService
{
    private $module;

    private $queryBuilder;

    public function __construct(HoneybeeModule $module)
    {
        $this->module = $module;
    }

    public function initialize($queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function fetchListData(IListConfig $config, IListState $state)
    {
        $query = $this->queryBuilder->build($config, $state);
        $offset = $state->getOffset();
        $limit = $state->getLimit();
        $repository = $this->module->getRepository();
        
        return $repository->find($query, $limit, $offset);
    }
}
