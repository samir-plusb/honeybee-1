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

     protected $docsJson = array(
     	'{
   "_id": "http://www.spiegel.de/wissenschaft/weltall/0,1518,794316,00.html",
   "_rev": "1-543d3c42852e34a43a1105fb7bb32311",
   "identifier": "http://www.spiegel.de/wissenschaft/weltall/0,1518,794316,00.html",
   "source": "SPIEGEL ONLINE - Schlagzeilen",
   "timestamp": "2011-10-27T13:43:11+0200",
   "title": "Chinesische Mission: \"Shenzhou 8\" bringt deutsche Experiment-Box ins All",
   "content": "China erobert den Weltraum - mit Hilfe aus Deutschland: An Bord des Raumschiffs &quot;Shenzhou 8&quot;, das nächste Woche ins All starten soll, befindet sich die deutsche Experimentieranlage Simbox. Es ist das erste Mal in der Geschichte der Raumfahrt, dass beide Nationen an einer Mission arbeiten. ",
   "category": null,
   "media": [
       316
   ],
   "geoData": [
   ],
   "author": null,
   "link": "http://www.spiegel.de/wissenschaft/weltall/0,1518,794316,00.html#ref=rss"
}',
     '{
   "_id": "http://www.spiegel.de/wissenschaft/weltall/0,1518,793386,00.html",
   "_rev": "1-7e3a56c1966a49e499572763ea060e8c",
   "identifier": "http://www.spiegel.de/wissenschaft/weltall/0,1518,793386,00.html",
   "source": "SPIEGEL ONLINE - Schlagzeilen",
   "timestamp": "2011-10-22T13:49:00+0200",
   "title": "Satellit \"Rosat\": Absturz bis Sonntagnachmittag erwartet",
   "content": "Irgendwann zwischen Samstagabend und Sonntagnachmittag soll es passieren: Dann wird der ausrangierte &quot;Rosat&quot;-Satellit abstürzen - womöglich über Deutschland. Die Risiken für Menschen dabei sind überschaubar, nur einziges schweres Trümmerteil könnte größeren Schaden anrichten.",
   "category": null,
   "media": [
   ],
   "geoData": [
   ],
   "author": null,
   "link": "http://www.spiegel.de/wissenschaft/weltall/0,1518,793386,00.html#ref=rss"
}',
     '{
   "_id": "http://www.spiegel.de/wissenschaft/weltall/0,1518,792934,00.html",
   "_rev": "1-7d16f397e3e359e61a3fbcdf1446cca2",
   "identifier": "http://www.spiegel.de/wissenschaft/weltall/0,1518,792934,00.html",
   "source": "SPIEGEL ONLINE - Schlagzeilen",
   "timestamp": "2011-10-20T16:24:57+0200",
   "title": "Massediebstahl: Kannibalismus hält blaue Sterne jung",
   "content": "Sie leuchten heller als ihre Nachbarn, obwohl sie gleich alt sind: Seit langem fragen sich Astronomen, was blaue Sterne jung hält. Ihre kosmischen Begleiter haben das Rätsel nun verraten - weil sie so zerfressen aussehen.",
   "category": null,
   "media": [
   ],
   "geoData": [
   ],
   "author": null,
   "link": "http://www.spiegel.de/wissenschaft/weltall/0,1518,792934,00.html#ref=rss"
}'
     );

     /**
      *
      * @var array
      */
     protected $docs;

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

         $this->docs = array();
         foreach ($this->docsJson as $json)
         {
         	$this->docs[] = json_decode($json, TRUE);
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
         $result = $this->client->getView($database, 'designTest', 'ticketByImportitem', array('key' => "boom"));
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
         $result = $this->client->getView(NULL, 'designTest', 'ticketByImportitem', array(
                 'key' => 'boom',
                 'include_docs' => TRUE));
         self::assertArrayHasKey('doc', $result['rows'][0]);
         self::assertEquals($this->document['_id'], $result['rows'][0]['doc']['_id']);
     }

     /**
      *
      *
      * @author tay
      * @since 29.10.2011
      */
     public function testStoreDocs()
     {
     	$this->client->createDatabase(NULL);
     	$result = $this->client->storeDocs(NULL, $this->docs);
		self::assertTrue(3 == count($result));
		foreach ($result as $dinfo)
		{
			self::assertArrayNotHasKey('error', $dinfo);
			self::assertArrayHasKey('id', $dinfo);
			self::assertArrayHasKey('rev', $dinfo);
		}
     }


     /**
      *
      *
      * @author tay
      * @since 29.10.2011
      */
     public function testStoreDocsConflict()
     {
     	$this->client->createDatabase(NULL);
     	$result = $this->client->storeDocs(NULL, $this->docs);
     	$docs = $this->docs;
     	unset($docs[1]['_rev']);
     	$result = $this->client->storeDocs(NULL, $docs);
		self::assertTrue(3 == count($result));
		foreach ($result as $idx => $dinfo)
		{
			if (1 == $idx)
			{
				self::assertArrayNotHasKey('error', $dinfo);
				self::assertArrayHasKey('id', $dinfo);
				self::assertArrayHasKey('rev', $dinfo);
			}
			else
			{
				self::assertArrayHasKey('error', $dinfo);
				self::assertArrayHasKey('id', $dinfo);
				self::assertArrayNotHasKey('rev', $dinfo);
			}
		}
     }

     /**
      *
      *
      * @author tay
      * @since 29.10.2011
      */
     public function testStoreDocsAllOrNothing()
     {
     	$this->client->createDatabase(NULL);
     	$result = $this->client->storeDocs(NULL, $this->docs, TRUE);
		self::assertTrue(3 == count($result));
		foreach ($result as $dinfo)
		{
			self::assertArrayNotHasKey('error', $dinfo);
			self::assertArrayHasKey('id', $dinfo);
			self::assertArrayHasKey('rev', $dinfo);
		}
     }


     /**
      *
      *
      * @author tay
      * @since 29.10.2011
      */
     public function testStoreDocsAllOrNothingConflict()
     {
     	$this->client->createDatabase(NULL);
     	$result = $this->client->storeDocs(NULL, $this->docs, TRUE);
     	$docs = $this->docs;
     	unset($docs[1]['_rev']);
     	$result = $this->client->storeDocs(NULL, $docs, TRUE);
		self::assertTrue(3 == count($result));
		foreach ($result as $dinfo)
		{
			self::assertArrayNotHasKey('error', $dinfo);
			self::assertArrayHasKey('id', $dinfo);
			self::assertArrayHasKey('rev', $dinfo);
		}
		// @todo check for conflicting documents at idx 0,2
     }
}

?>