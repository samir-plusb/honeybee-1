<?php

namespace Honeybee\Core\Service;

use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Dat0r\Tree;
use Honeybee\Core\Job\Job\UpdateBackReferencesJob;
use Honeybee\Core\Job\Job\JobQueue;
use Honeybee\Core\Finder\ElasticSearch;
use Elastica;

use ListConfig;
use IListState;

class DocumentService implements IService
{
    private $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function save(Document $document)
    {
        // @todo check if $this->module === $document->getModule() and throw exception if they dont match
        $changed_fields = array();
        foreach ($document->getChanges() as $change_event) {
            $changed_fields[] = $change_event->getField()->getName();
        }

        $repository = $this->module->getRepository();
        $errors = $repository->write($document);

        if (!empty($errors)) {
            throw new Exception(
                "Unexpected errors occured trying to store data." .
                PHP_EOL . implode("\n", $errors)
            );
        }

        // $this->updateReferencingDocuments($document, $changed_fields);
    }

    public function get($identifier)
    {
        $document = null;

        $repository = $this->module->getRepository();
        $documents = $repository->read($identifier);

        if (1 === $documents->count()) {
            $document = $documents[0];
        }

        return $document;
    }

    public function getMany(array $identifiers = array(), $limit = 10000, $offset = 0)
    {
        $query = null;

        if (empty($identifiers)) {
            $query = Elastica\Query::create(null);
        } else {
            $container = new Elastica\Filter\BoolAnd();
            $container->addFilter(new Elastica\Filter\Ids(
                $this->module->getOption('prefix'),
                array_unique($identifiers)
            ));
            $container->addFilter(new Elastica\Filter\BoolNot(
                new Elastica\Filter\Term(
                    array('meta.is_deleted' => true)
                )
            ));
            $query = Elastica\Query::create($container);
        }

        $repository = $this->module->getRepository();

        return $repository->find($query, $limit, $offset);
    }

    public function fetchAll($offset, $limit)
    {
        $repository = $this->module->getRepository();

        return $repository->find(null, $limit, $offset);
    }

    public function find(array $spec, $offset, $limit)
    {
        $queryBuilder = new ElasticSearch\DefaultQueryBuilder();
        $query = $queryBuilder->build($spec);
        $repository = $this->module->getRepository();

        return $repository->find($query, $limit, $offset);
    }

    public function delete(Document $document, $markOnly = true)
    {
        if ($markOnly) {
            $meta = $document->getMeta();
            $meta['is_deleted'] = true;
            $document->setMeta($meta);

            $this->save($document);
        } else {
            // this actually is destructive, only use if you REALLY want to delete.
            $this->module->getRepository()->delete($document);
        }
    }

    public function fetchListData(ListConfig $config, IListState $state)
    {
        // @todo Introduce a factory setting to allow inject the implementor for building queries.
        $queryBuilderClass = $config->getQueryBuilder();
        $queryBuilder = new $queryBuilderClass();
        $query = $queryBuilder->build(array('config' => $config, 'state' => $state));

        $offset = $state->getOffset();
        $limit = $state->getLimit();
        $repository = $this->module->getRepository();

        return $repository->find($query, $limit, $offset);
    }

    public function suggestDocuments($term, $field, $sorting = array())
    {
        $repository = $this->module->getRepository();
        $queryBuilder = new ElasticSearch\SuggestQueryBuilder();
        $query = $queryBuilder->build(
            array('term' => $term, 'field' => $field, 'sorting' => $sorting)
        );

        return $repository->find($query, 50, 0);
    }

    public function getModule()
    {
        return $this->module;
    }

    public function updateReferencingDocuments(Document $document, array $changed_fields = array())
    {
        // update all dependent referencing index fields if required.
        $dispatch_update_job = false;
error_log(__CLASS__ . ' ' . print_r($changed_fields, true));
error_log("---");
        $referencing_modules = $this->module->getReferencingFieldIndices();
        foreach ($referencing_modules as $reference_meta_data) {
            $unaffected_fields = array_diff($changed_fields, $reference_meta_data['index_fields']);
error_log(__CLASS__ . ' ' . print_r($reference_meta_data['index_fields'], true));
error_log(__CLASS__ . ' ' . print_r($unaffected_fields, true));
error_log("------------");
            if (empty($changed_fields) || count($unaffected_fields) < count($changed_fields)) {
                $dispatch_update_job = true;
                break;
            }
        }

        if ($dispatch_update_job) {
            // @todo it could be faster to have a job per referencing module, instead of one for all.
            $queue = new JobQueue('prio:1-default_queue');
            $job_data = array(
                'module_class' => get_class($this->module),
                'document_identifier' => $document->getIdentifier()
            );
            $queue->push(new UpdateBackReferencesJob($job_data));
        }
    }
}
