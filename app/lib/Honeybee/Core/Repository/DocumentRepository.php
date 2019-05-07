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
    public function find($query = null, $limit = 100000, $offset = 0, $fly = false)
    {
        if ($fly)
        {
            # fly through and do not traverse deep references
            RelationManager::setRecursionDepth(2);
            RelationManager::setMaxRecursionDepth(0);
            # error_log('flight mode');
        }

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
            // backreference (delete values in referencing modules)
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
        if(is_int(strpos($identifier, 'category'))){ # the actual identifier like category-967faafd-61b1-4062-ba2e-5ba41d66cafe-de_DE-1
            array_push($referencesToUpdate, 'topics'); # look in topics
            array_push($referencesToUpdate, 'news'); # look in news
//            array_push($referencesToUpdate, 'guides'); # look in guides field (of this category)
            array_push($referencesToUpdate, 'localities'); # honeybee architecture is not capable of handling so many backreferences
//            array_push($referencesToUpdate, 'downloads');
//            array_push($referencesToUpdate, 'events');
//            array_push($referencesToUpdate, 'externalPages');
            $index = 'categories'; # look for categories field in specified modules
        } elseif(is_int(strpos($identifier, 'download'))){
            array_push($referencesToUpdate, 'topics');
            array_push($referencesToUpdate, 'news');
//            array_push($referencesToUpdate, 'categories');
            $index = 'downloads';
        } elseif(is_int(strpos($identifier, 'event'))){
            array_push($referencesToUpdate, 'localities');
//            array_push($referencesToUpdate, 'categories');
            $index = 'events';
        } elseif(is_int(strpos($identifier, 'external_link'))) {
            array_push($referencesToUpdate, 'topics');
            array_push($referencesToUpdate, 'guides');
            array_push($referencesToUpdate, 'news');
//            array_push($referencesToUpdate, 'categories');
            $index = 'externalPages';
        } elseif(is_int(strpos($identifier, 'guide'))){
//            array_push($referencesToUpdate, 'categories');
            $index = 'guides';
        } elseif(is_int(strpos($identifier, 'locality'))){
            array_push($referencesToUpdate, 'events');
//            array_push($referencesToUpdate, 'topics');
//            array_push($referencesToUpdate, 'news');
            array_push($referencesToUpdate, 'categories');
            $index = 'localities';
        } elseif(is_int(strpos($identifier, 'news'))){
//            array_push($referencesToUpdate, 'localities');
//            array_push($referencesToUpdate, 'categories');
//            array_push($referencesToUpdate, 'topics');
//            array_push($referencesToUpdate, 'downloads');
            $index = 'news';
        } elseif(is_int(strpos($identifier, 'topic'))){
//            array_push($referencesToUpdate, 'localities');
//            array_push($referencesToUpdate, 'categories');
//            array_push($referencesToUpdate, 'downloads');
//            array_push($referencesToUpdate, 'externalPages');
            $index = 'topics';
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
            case 'categories':
                $db = "famport_production_category";
                break;
            case 'downloads':
                $db = "famport_production_download";
                break;
            case 'events':
                $db = "production_famport_event";
                break;
            case 'externalPages':
                $db = "famport_production_external_link";
                break;
            case 'guides':
                $db = "production_famport_guide";
                break;
            case 'localities':
                $db = "production_famport_locality";
                break;
            case 'news':
                $db = "famport_production_news";
                break;
           case 'topics':
                $db = "famport_production_topic";
                break;
        }
        $con = $this->getStorage()->getDatabase()->getConnection();
        $allDocs = $con->getAllDocs($db, array('include_docs' => true));
        foreach ($allDocs['rows'] as $document) {
            if(!empty($document['doc'][$field])){
                $documentId = $document['doc']['_id'];
                $references = $document['doc'][$field];
                $meta = $document['doc']['meta'];
                if ( empty($meta) || !array_key_exists('is_deleted', $meta) ) { # don't connect deleted documents
                    foreach ($references as $reference) {
                        if ($reference['id'] === $identifier) {
                            $this->makeBackReference($identifier, $documentId, $module);
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
        $alreadyLinked = false;
        if(array_key_exists($field, $data)){
            foreach ($data[$field] as $existingReference) {
                if($existingReference['id'] === $referenceId){
                    $alreadyLinked = true;
                }
            }
        }
        if(!$alreadyLinked){
            $newReference = array("id" => $referenceId, "module" => $this->convertPlural($field));
            $data[$field][] = $newReference;
            $document = $this->getModule()->createDocument($data);
            $this->write($document);
            error_log('______________________________________________');
            error_log('connect ' . $referenceId . ' d-i ' . $identifier);
        }
    }

    /** Convert field name to module name
     *
     * @param $fieldName
     * @return string
     */
    public function convertPlural($fieldName){
        $singular = "category";
        switch($fieldName){
            case "downloads":
                $singular = "download";
                break;
            case "events":
                $singular = "event";
                break;
            case "externalPages":
                $singular = "externalLink";
                break;
            case "guides":
                $singular = "guide";
                break;
            case "localities":
                $singular = "locality";
                break;
            case "news":
                $singular = "news";
                break;
            case "topics":
                $singular = "topic";
                break;
        }
        return $singular;
    }
}
