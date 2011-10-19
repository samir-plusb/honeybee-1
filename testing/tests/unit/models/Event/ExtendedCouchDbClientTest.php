<?php
/**
 * tests the class ExtendedCouchDbClient
 *
 * @package Testing
 * @subpackage Models
 * @author tay
 * @version $Id$
 * @since 13.10.2011
 *
 */
class ExtendedCouchDbClientTest extends AgaviPhpUnitTestCase
{
    const DATABASE = 'unittest';

    /**
     * couch base url
     */
    const BASEURL = 'http://127.0.0.1:5984/';

    /**
     * document fixture
     */
    protected $document = array(
        '_id' => 'docid.0',
        'title' => 'foobar',
        'importItem' => 'boom',
        'tags' => array('blah', 'fasel', 'tröt')
    );

    /**
     * @var design document fixture
     */
    protected $designDoc = array(
        "_id" => "_design/designWorkflow",
        "views" => array(
            "ticketByImportitem" => array(
                "map" => "function(doc)
                    {
                        emit(doc.importItem, doc._id);
                    }"
                )
           )
     );

     /**
      *
      * @var ExtendedCouchDbClient
      */
     protected $client;

     public function setup()
     {
         $ch = ProjectCurl::create();
         curl_setopt($ch, CURLOPT_PROXY, '');
         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
         curl_setopt($ch, CURLOPT_URL, self::BASEURL.self::DATABASE.'/');
         curl_exec($ch);
         $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
         if (404 != $status && 200 != $status)
         {
             throw new Exception("Setup failed with: ($status) ".curl_error($ch));
         }

         $this->client = new ExtendedCouchDbClient(self::BASEURL, self::DATABASE);
     }


     public function tearDown()
     {
         $this->setup();
     }

     public function provideDatabaseParameter()
     {
         return array(
             array('database' => self::DATABASE),
             array('database' => NULL)
         );
     }

     /**
      */
     public function testGetDatabaseName()
     {
         self::assertEquals(self::DATABASE, $this->client->getDatabaseName());
     }

     /**
      *
      */
     public function testCreateDatabaseNew()
     {
         self::assertTrue($this->client->createDatabase(self::DATABASE));
     }

     /**
      *
      */
     public function testCreateDatabaseExistsUseDefaultDatabaseName()
     {
         $this->client->createDatabase(NULL);
         self::assertFalse($this->client->createDatabase(self::DATABASE));
     }

     /**
      *
      */
     public function testCreateDatabaseExists()
     {
         $this->client->createDatabase(NULL);
         self::assertFalse($this->client->createDatabase(NULL));
     }


     /**
      *
      * @dataProvider provideDatabaseParameter
      */
     public function testCreateDatabaseMissing($database)
     {
         self::assertFalse($this->client->deleteDatabase($database));
     }

     /**
      *
      */
     public function testDeleteDatabaseExists()
     {
         $this->client->createDatabase(NULL);
         self::assertTrue($this->client->deleteDatabase(NULL));
     }


     /**
      *
      * @dataProvider provideDatabaseParameter
      */
     public function testGetDatabaseMissing($database)
     {
         self::assertFalse($this->client->getDatabase($database));
     }


     /**
      *
      * @dataProvider provideDatabaseParameter
      */
     public function testGetDatabaseExists($database)
     {
         $this->client->createDatabase($database);
         self::assertArrayHasKey('db_name', $this->client->getDatabase($database));
     }


     /**
      *
      * @dataProvider provideDatabaseParameter
      */
     public function testCreateDocument($database)
     {
         $this->client->createDatabase($database);
         $status = $this->client->storeDoc($database, $this->document);
         self::assertArrayHasKey('ok', $status);
         self::assertArrayHasKey('rev', $status);
     }

     /**
      *
      */
     public function testCreateDocumentNumericId()
     {
         $this->client->createDatabase(NULL);
         $document = $this->document;
         $document['_id'] = 42;
         $status = $this->client->storeDoc(NULL, $document);
         self::assertArrayHasKey('ok', $status);
         self::assertArrayHasKey('rev', $status);
     }

     /**
      *
      */
     public function testCreateDocumentAutoId()
     {
         $this->client->createDatabase(NULL);
         $document = $this->document;
         unset($document['_id']);
         $status = $this->client->storeDoc(NULL, $document);
         self::assertArrayHasKey('ok', $status);
         self::assertArrayHasKey('id', $status);
         self::assertArrayHasKey('rev', $status);
     }

     /**
      *
      */
     public function testCreateDocumentConflict()
     {
         $this->client->createDatabase(NULL);
         $this->client->storeDoc(NULL, $this->document);
         $status = $this->client->storeDoc(NULL, $this->document);
         self::assertArrayHasKey('error', $status);
         self::assertEquals('conflict', $status['error']);
     }

     /**
      *
      * @dataProvider provideDatabaseParameter
      */
     public function testGetDocument($database)
     {
         $this->client->createDatabase($database);
         $this->client->storeDoc($database, $this->document);
         $returned_document = $this->client->getDoc($database, $this->document['_id']);
         self::assertArrayHasKey('_rev', $returned_document);
         unset($returned_document['_rev']);
         self::assertEquals($this->document, $returned_document);
     }

     /**
      *
      * @dataProvider provideDatabaseParameter
      */
     public function testStatDocument($database)
     {
         $this->client->createDatabase($database);
         $this->client->storeDoc($database, $this->document);
         $revision = $this->client->statDoc($database, $this->document['_id']);
         self::assertRegExp('/\d+-[a-f\d]+$/', $revision);
     }

     /**
      *
      */
     public function testStatDocumentMissingDocument()
     {
         $this->client->createDatabase(NULL);
         $revision = $this->client->statDoc(NULL, $this->document['_id']);
         self::assertEquals(0, $revision);
     }

     /**
      *
      * @dataProvider provideDatabaseParameter
      */
     public function testUpdateDocument()
     {
         $this->client->createDatabase(NULL);
         $this->client->storeDoc(NULL, $this->document);
         $revistion = $this->client->statDoc(NULL, $this->document['_id']);
         $document = $this->document;
         $document['_rev'] = $revistion;
         $result = $this->client->storeDoc(NULL, $document);
         self::assertArrayHasKey('ok', $result);
         self::assertRegExp('/^2-/', $result['rev']);
     }


     /**
      *
      */
     public function testGetDocumentRelease()
     {
         $this->client->createDatabase(NULL);
         $this->client->storeDoc(NULL, $this->document);
         $revistion = $this->client->statDoc(NULL, $this->document['_id']);
         $document = $this->document;
         $document['_rev'] = $revistion;
         $this->client->storeDoc(NULL, $document);
         $result = $this->client->getDoc(NULL, $this->document['_id'], $revistion);
         self::assertEquals($this->document['_id'], $result['_id']);
         self::assertEquals($revistion, $result['_rev']);
     }

     /**
      *
      * @dataProvider provideDatabaseParameter
      */
     public function testCreateDesignDocument($database)
     {
         $this->client->createDatabase($database);
         self::assertArrayHasKey('ok', $this->client->createDesignDocument($database, 'designTest', $this->designDoc));
     }

     /**
      *
      */
     public function testCreateDesignDocumentConflict()
     {
         $this->client->createDatabase(NULL);
         $this->client->createDesignDocument(NULL, 'designTest', $this->designDoc);
         self::assertArrayHasKey('error', $this->client->createDesignDocument(NULL, 'designTest', $this->designDoc));
     }

     /**
      *
      * @dataProvider provideDatabaseParameter
      */
     public function testGetDesignDocument($database)
     {
         $this->client->createDatabase($database);
         $this->client->createDesignDocument($database, 'designTest', $this->designDoc);
         $stat =  $this->client->getDesignDocument($database, 'designTest');
         self::assertArrayHasKey('_id', $stat);
         self::assertArrayHasKey('_rev', $stat);
     }

     /**
      *
      */
     public function testUpdateDesignDocument()
     {
         $this->client->createDatabase(NULL);
         $this->client->createDesignDocument(NULL, 'designTest', $this->designDoc);
         $stat =  $this->client->getDesignDocument(NULL, 'designTest');
         $document = $this->designDoc;
         $document['_rev'] = $stat['_rev'];
         self::assertArrayHasKey('ok', $this->client->createDesignDocument(NULL, 'designTest', $document));
     }

     /**
      *
      * @dataProvider provideDatabaseParameter
      */
     public function testGetView($database)
     {
         $this->client->createDatabase($database);
         $this->client->createDesignDocument($database, 'designTest', $this->designDoc);
         $this->client->storeDoc($database, $this->document);
         $result = $this->client->getView($database, 'designTest', 'ticketByImportitem', json_encode("boom"));
         self::assertArrayHasKey('total_rows', $result);
         self::assertArrayHasKey('rows', $result);
         self::assertEquals($result['total_rows'], count($result['rows']));
         self::assertEquals($this->document['_id'], $result['rows'][0]['id']);
     }

     /**
      *
      */
     public function testGetViewIncludeDocument()
     {
         $this->client->createDatabase(NULL);
         $this->client->createDesignDocument(NULL, 'designTest', $this->designDoc);
         $this->client->storeDoc(NULL, $this->document);
         $result = $this->client->getView(NULL, 'designTest', 'ticketByImportitem',
             json_encode("boom"), 0, array('include_docs' => 'true'));
         self::assertArrayHasKey('doc', $result['rows'][0]);
         self::assertEquals($this->document['_id'], $result['rows'][0]['doc']['_id']);
     }
}

?>