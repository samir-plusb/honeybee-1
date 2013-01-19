<?php

abstract class BaseService implements IService
{
    private $module;

    public function __construct(HoneybeeModule $module)
    {
        $this->module = $module;
    }

    public function save(HoneybeeDocument $document)
    {
        $repository = $this->module->getRepository();
        $errors = $repository->write($document);

        if (! empty($errors))
        {
            throw new Exception(
                "Unexpected errors occured trying to store data." . 
                PHP_EOL . implode("\n", $errors)
            );
        }
    }

    public function get($identifier)
    {
        $document = NULL;

        $repository = $this->module->getRepository();
        $documents = $repository->read($identifier);

        if (1 === $documents->count())
        {
            $document = $documents[0];
        }

        return $document;
    }

    public function delete(HoneybeeDocument $document, $markOnly = TRUE)
    {
        if ($markOnly)
        {
            $meta = $document->getMeta();
            $meta['is_deleted'] = TRUE;
            $document->setMeta($meta);
            
            $this->save($document);
        }
        else
        {
            // this actually is destructive, only use if you REALLY want to delete.
            $this->module->getRepository()->delete($document);
        }
    }

    public function fetchListData(IListConfig $config, IListState $state)
    {
        $queryBuilder = new ElasticaQueryBuilder();
        $query = $queryBuilder->build(
            array('config' => $config, 'state' => $state)
        );

        $offset = $state->getOffset();
        $limit = $state->getLimit();
        $repository = $this->module->getRepository();
        
        return $repository->find($query, $limit, $offset);
    }

    public function suggestDocuments($term, $field, $sorting = array())
    {
        $repository = $this->module->getRepository();
        $queryBuilder = new SuggestQueryBuilder();
        $query = $queryBuilder->build(
            array('term' => $term, 'field' => $field, 'sorting' => $sorting)
        );

        return $repository->find($query, 50, 0);
    }
}
