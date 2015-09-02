<?php

namespace Honeybee\Core\Repository;

use Honeybee\Agavi\Database\CouchDb\Database;
use Honeybee\Core\Finder\IFinder;
use Honeybee\Core\Storage\CouchDb\DocumentStorage;
use Honeybee\Core\Storage\IStorage;
use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Dat0r\ModuleService;
use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Dat0r\DocumentCollection;
use Honeybee\Core\Dat0r\RelationManager;
use Honeybee\Domain\Topic\TopicModule;

class DocumentRepository extends BaseRepository
{
    public function find($query = null, $limit = 100000, $offset = 0)
    {
        $documents = new DocumentCollection();

        $result = (null === $query)
            ? $this->getFinder()->fetchAll($limit, $offset)
            : $this->getFinder()->find($query, $limit, $offset);

        $max_depth = RelationManager::getMaxRecursionDepth();
        if (-1 === $max_depth|| RelationManager::getRecursionDepth() <= $max_depth) {
            RelationManager::prePopulateReferences($this->getModule(), $result['data']);
        }

        foreach ($result['data'] as $document_data) {
            $documents->add($this->getModule()->createDocument($document_data));
        }

        return array(
            'documents' => $documents,
            'totalCount' => $result['totalCount']
        );
    }

    // @todo add a get method to the finder and use it instead of the storage here.
    public function read($identifier)
    {
        //update category/topic relation
        $this->updateCategoryReferences($identifier);

        $documents = new DocumentCollection();

        if (is_array($identifier)) {
            foreach ($identifier as $current_identifier) {
                if (($data = $this->getStorage()->read($current_identifier))) {
                    $documents->add(
                        $this->getModule()->createDocument($data)
                    );
                }
            }
        } elseif (($data = $this->getStorage()->read($identifier))) {
            $documents->add(
                $this->getModule()->createDocument($data)
            );
        }

        return $documents;
    }

    public function write($document)
    {
        if ($document instanceof Document) {
            $document->checkMandatoryFields();

            $document->onBeforeWrite();
            $data = $document->toArray();
            // backreference
            $data['type'] = get_class($document);
            $revision = $this->getStorage()->write($data);
            $document->setRevision($revision);
            $document->onAfterWrite();
        } else {
            throw new \InvalidArgumentException('Only Document instances are allowed as $data argument.');
        }
    }

    public function delete($document)
    {
        $errors = array();

        if ($document instanceof Document) {
            $this->getStorage()->delete($$document->getIdentifier(), $document->getRevision());
        } else {
            throw new \InvalidArgumentException('Only Document instances allowed for the $data method parameter.');
        }

        return $errors;
    }


    public function updateCategoryReferences($identifier){
        if(is_int(strpos($identifier, 'category'))){
            $con = $this->getStorage()->getDatabase()->getConnection();
            $alltopics = $con->getAllDocs("famport_production_topic", array('include_docs' => true));
            foreach ($alltopics['rows'] as $topic) {
                if(!empty($topic['doc']['categories'])){
                    $topicId = $topic['doc']['_id'];
                    $categories = $topic['doc']['categories'];
                    foreach ($categories as $category) {
                        if($category['id'] === $identifier){
                            $this->makeBackReference($identifier, $topicId, 'topics');
                        }
                    }
                }
            }
        }
    }

    /** Check references and make back-reference if it doesn't exist.
     *
     * @param string $identifier the elements id
     * @param string $referenceId the id of the referencing element
     * @param string $field the type of the referencing element
     */
    public function makeBackReference($identifier, $referenceId, $field){
        $data = $this->getStorage()->read($identifier);
        $allreadyLinked = false;
        foreach ($data[$field] as $existingReference) {
            if($existingReference['id'] === $referenceId){
                $allreadyLinked = true;
            }
        }
        if(!$allreadyLinked){
            $newReference = array("id" => $referenceId, "module" => $this->convertPlural($field));
            $data[$field][] = $newReference;
            $document = $this->getModule()->createDocument($data);
            $this->write($document);
        }
    }

    /** Convert field name to module name
     *
     * @param $fieldName
     * @return string
     */
    public function convertPlural($fieldName){
        $singular = "topic";
        switch($fieldName){
            case "topics":
                $singular = "topic";
                break;
        }
        return $singular;
    }
}
