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
     *
     * @var unknown_type
     */
    const BASEURL = 'http://127.0.0.1:5984/';

    /**
     * document fixture
     */
    protected $document = array(
        '_id' => 'docid.0',
        'title' => 'foobar',
        'tags' => array('blah', 'fasel', 'tröt')
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
}

?>