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
        //update relation display in backend
        $this->updateExtReferences($identifier);

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
            // backreference (delete values in referencing modules )
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


    public function updateExtReferences($identifier){
        $referencesToUpdate = array();
        $index = "";
        if(is_int(strpos($identifier, 'category')) || is_int(strpos($identifier, 'download'))){ //categories display referencing topics
            array_push($referencesToUpdate, 'topics');
            array_push($referencesToUpdate, 'news');
            $index = is_int(strpos($identifier, 'category')) ? 'category' : 'downloads';
        } elseif(is_int(strpos($identifier, 'external_link'))) { //categories display referencing topics, guides, news
            array_push($referencesToUpdate, 'topics');
            array_push($referencesToUpdate, 'guides');
            array_push($referencesToUpdate, 'news');
            $index = 'externalPages';
        }
        if(count($referencesToUpdate) > 0){
            foreach ($referencesToUpdate as $ref) {
                $this->connectReferences($identifier, $ref, $index);
            }
        }
    }

    /** get external db -> read and update references to current module
     * @param $identifier
     * @param $module
     * @param $field
     */
    public function connectReferences($identifier, $module, $field){
        // since there is no easy way to get the couchdbnames...
        switch($module){
            case 'topics':
                $db = "famport_production_topic";
                break;
            case 'news':
                $db = "famport_production_news";
                break;
            case 'guides':
                $db = "production_famport_guide";
                break;
        }
        $con = $this->getStorage()->getDatabase()->getConnection();
        $allDocs = $con->getAllDocs($db, array('include_docs' => true));
        foreach ($allDocs['rows'] as $document) {
            if(!empty($document['doc'][$field])){
                $documentId = $document['doc']['_id'];
                $references = $document['doc'][$field];
                foreach ($references as $reference) {
                    if($reference['id'] === $identifier){
                        $this->makeBackReference($identifier, $documentId, $module);
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
        if(array_key_exists($field, $data)){
            foreach ($data[$field] as $existingReference) {
                if($existingReference['id'] === $referenceId){
                    $allreadyLinked = true;
                }
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
            case "guides":
                $singular = "guide";
                break;
            case "news":
                $singular = "news";
                break;

        }
        return $singular;
    }
}
