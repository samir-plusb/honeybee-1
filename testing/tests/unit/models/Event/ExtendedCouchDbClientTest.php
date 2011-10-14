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

         $this->client = new ExtendedCouchDbClient(self::BASEURL);
     }


     public function tearDown()
     {
         $this->setup();
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
     public function testCreateDatabaseExists()
     {
         $this->client->createDatabase(self::DATABASE);
         self::assertFalse($this->client->createDatabase(self::DATABASE));
     }


     /**
      *
      */
     public function testCreateDatabaseMissing()
     {
         self::assertFalse($this->client->deleteDatabase(self::DATABASE));
     }

     /**
      *
      */
     public function testDeleteDatabaseExists()
     {
         $this->client->createDatabase(self::DATABASE);
         self::assertTrue($this->client->deleteDatabase(self::DATABASE));
     }


     /**
      *
      */
     public function testGetDatabaseMissing()
     {
         self::assertFalse($this->client->getDatabase(self::DATABASE));
     }


     /**
      *
      */
     public function testGetDatabaseExists()
     {
         $this->client->createDatabase(self::DATABASE);
         self::assertArrayHasKey('db_name', $this->client->getDatabase(self::DATABASE));
     }


     /**
      *
      */
     public function testCreateDocument()
     {
         $this->client->createDatabase(self::DATABASE);
         $status = $this->client->storeDoc(self::DATABASE, $this->document);
         self::assertArrayHasKey('ok', $status);
         self::assertArrayHasKey('rev', $status);
     }

     /**
      *
      */
     public function testCreateDocumentNumericId()
     {
         $this->client->createDatabase(self::DATABASE);
         $document = $this->document;
         $document['_id'] = 42;
         $status = $this->client->storeDoc(self::DATABASE, $document);
         self::assertArrayHasKey('ok', $status);
         self::assertArrayHasKey('rev', $status);
     }

     /**
      *
      */
     public function testCreateDocumentAutoId()
     {
         $this->client->createDatabase(self::DATABASE);
         $document = $this->document;
         unset($document['_id']);
         $status = $this->client->storeDoc(self::DATABASE, $this->document);
         self::assertArrayHasKey('ok', $status);
         self::assertArrayHasKey('id', $status);
         self::assertArrayHasKey('rev', $status);
     }

     /**
      *
      */
     public function testCreateDocumentConflict()
     {
         $this->client->createDatabase(self::DATABASE);
         $this->client->storeDoc(self::DATABASE, $this->document);
         $status = $this->client->storeDoc(self::DATABASE, $this->document);
         self::assertArrayHasKey('error', $status);
         self::assertEquals('conflict', $status['error']);
     }

     /**
      *
      */
     public function testGetDocument()
     {
         $this->client->createDatabase(self::DATABASE);
         $this->client->storeDoc(self::DATABASE, $this->document);
         $returned_document = $this->client->getDoc(self::DATABASE, $this->document['_id']);
         self::assertArrayHasKey('_rev', $returned_document);
         unset($returned_document['_rev']);
         self::assertEquals($this->document, $returned_document);
     }

     /**
      *
      */
     public function testStatDocument()
     {
         $this->client->createDatabase(self::DATABASE);
         $this->client->storeDoc(self::DATABASE, $this->document);
         $revision = $this->client->statDoc(self::DATABASE, $this->document['_id']);
         self::assertRegExp('/\d+-[a-f\d]+$/', $revision);
     }

     /**
      *
      */
     public function testStatDocumentMissingDocument()
     {
         $this->client->createDatabase(self::DATABASE);
         $revision = $this->client->statDoc(self::DATABASE, $this->document['_id']);
         self::assertEquals(0, $revision);
     }

     /**
      *
      */
     public function testUpdateDocument()
     {
         $this->client->createDatabase(self::DATABASE);
         $this->client->storeDoc(self::DATABASE, $this->document);
         $revistion = $this->client->statDoc(self::DATABASE, $this->document['_id']);
         $document = $this->document;
         $document['_rev'] = $revistion;
         $result = $this->client->storeDoc(self::DATABASE, $document);
         self::assertArrayHasKey('ok', $result);
         self::assertRegExp('/^2-/', $result['rev']);
     }


     /**
      *
      */
     public function testGetDocumentRelease()
     {
         $this->client->createDatabase(self::DATABASE);
         $this->client->storeDoc(self::DATABASE, $this->document);
         $revistion = $this->client->statDoc(self::DATABASE, $this->document['_id']);
         $document = $this->document;
         $document['_rev'] = $revistion;
         $this->client->storeDoc(self::DATABASE, $document);
         $result = $this->client->getDoc(self::DATABASE, $this->document['_id'], $revistion);
         self::assertEquals($this->document['_id'], $result['_id']);
         self::assertEquals($revistion, $result['_rev']);
     }

     /**
      *
      */
     public function testCreateDesignDocument()
     {
         $this->client->createDatabase(self::DATABASE);
         self::assertArrayHasKey('ok', $this->client->createDesignDocument(self::DATABASE, 'designTest', $this->designDoc));
     }

     /**
      *
      */
     public function testCreateDesignDocumentConflict()
     {
         $this->client->createDatabase(self::DATABASE);
         $this->client->createDesignDocument(self::DATABASE, 'designTest', $this->designDoc);
         self::assertArrayHasKey('error', $this->client->createDesignDocument(self::DATABASE, 'designTest', $this->designDoc));
     }

     /**
      *
      */
     public function testGetDesignDocument()
     {
         $this->client->createDatabase(self::DATABASE);
         $this->client->createDesignDocument(self::DATABASE, 'designTest', $this->designDoc);
         $stat =  $this->client->getDesignDocument(self::DATABASE, 'designTest');
         self::assertArrayHasKey('_id', $stat);
         self::assertArrayHasKey('_rev', $stat);
     }

     /**
      *
      */
     public function testUpdateDesignDocument()
     {
         $this->client->createDatabase(self::DATABASE);
         $this->client->createDesignDocument(self::DATABASE, 'designTest', $this->designDoc);
         $stat =  $this->client->getDesignDocument(self::DATABASE, 'designTest');
         $document = $this->designDoc;
         $document['_rev'] = $stat['_rev'];
         self::assertArrayHasKey('ok', $this->client->createDesignDocument(self::DATABASE, 'designTest', $document));
     }

     /**
      *
      */
     public function testGetView()
     {
         $this->client->createDatabase(self::DATABASE);
         $this->client->createDesignDocument(self::DATABASE, 'designTest', $this->designDoc);
         $this->client->storeDoc(self::DATABASE, $this->document);
         $result = $this->client->getView(self::DATABASE, 'designTest', 'ticketByImportitem', json_encode("boom"));
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
         $this->client->createDatabase(self::DATABASE);
         $this->client->createDesignDocument(self::DATABASE, 'designTest', $this->designDoc);
         $this->client->storeDoc(self::DATABASE, $this->document);
         $result = $this->client->getView(self::DATABASE, 'designTest', 'ticketByImportitem',
             json_encode("boom"), 0, array('include_docs' => 'true'));
         self::assertArrayHasKey('doc', $result['rows'][0]);
         self::assertEquals($this->document['_id'], $result['rows'][0]['doc']['_id']);
     }
}

?>