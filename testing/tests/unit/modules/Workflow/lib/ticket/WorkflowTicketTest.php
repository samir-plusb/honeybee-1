<?php

/**
 * test supervisor
 *
 * @package Testing
 * @subpackage Workflow
 * @author tay
 * @version $Id$
 * @since 25.10.2011
 *
 */
class WorkflowTicketTest extends AgaviUnitTestCase
{
    const RECORD = '{"_id":"33112544","_rev":"1-66239a2f4c4d9a47e1b622484f308429","identifier":"33112544","source":"dpa - Deutsche Presse-Agentur GmbH","timestamp":"2011-10-20T15:15:01+0200","title":"Merkel f\u00fcr Bund-L\u00e4nder-Zusammenarbeit bei Integration","content":"Berlin (dpa/bb) - Der Bund will beim Thema Integration in der Schule eng mit den L\u00e4ndern zusammenarbeiten. \u00abWir gehen hier nicht nach Zust\u00e4ndigkeiten\u00bb, sagte Bundeskanzlerin Angela Merkel (CDU) am Donnerstag bei einem Besuch der Erika-Mann-Grundschule in Berlin-Wedding. \u00abWir wollen Hand in Hand arbeiten, damit jedes Kind in Deutschland einen Ausbildungsplatz bekommt.\u00bb Am Nachmittag wollte Merkel bei der Kultusministerkonferenz dabei sein, die sich mit der Integrationspolitik besch\u00e4ftigt. Die Erika-Mann-Schule hat einen hohen Anteil an Sch\u00fclern mit ausl\u00e4ndischen Wurzeln und gilt als Vorzeigeschule.","category":"/regioline/berlinbrandenburg/","media":[],"geoData":[],"subtitle":"","abstract":null,"keywords":["Bildung","Kultusministerkonferenz"],"copyright":"dpa-info.com GmbH","release":null,"expire":null,"table":[],"links":["<a href=\"http://dpaq.de/m4Bjt\">Erika-Mann-Grundschule</a>","<a href=\"http://dpaq.de/amPDH\">Pressemitteilung Senatsverwaltung</a>"]}';

    /**
     *
     * @var WorkflowItem
     */
    var $record;

    /**
     *
     * @var IEvent
     */
    var $event;

    /**
     *
     * @var Workflow_SupervisorModel
     */
    protected $supervisor;

    /**
     *
     * @var WorkflowTicketPeer
     */
    protected $peer;


    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        $this->supervisor = Workflow_SupervisorModel::getInstance();
        $this->record = new WorkflowItem(json_decode(self::RECORD,TRUE));
        $this->peer = $this->supervisor->getTicketPeer();

        $setup = new WorkflowModuleSetup();
        $setup->setup(TRUE);

        $itemsDb = $this->supervisor->getItemPeer()->getDatabase();
        $itemsDb->storeDoc(NULL, json_decode(self::RECORD,TRUE));
    }

    /**
     *
     */
    public function testInstance()
    {
        self::assertEquals($this->peer, $this->supervisor->getTicketPeer());
    }

    /**
     *
     */
    public function testCreateNewTicketFromImportItem()
    {
        $ticket = $this->peer->createNewTicketFromImportItem($this->record);
        self::assertInstanceOf('WorkflowTicket', $ticket);
        self::assertInstanceOf('IDataRecord', $ticket->getImportItem());
        self::assertNotEmpty($ticket->getIdentifier());
        self::assertNotEmpty($ticket->getRevision());
        self::assertEquals($this->record->getIdentifier(), $ticket->getImportItem()->getIdentifier());
    }


    /**
     *
     */
    public function testGet()
    {
        $ticket = $this->peer->createNewTicketFromImportItem($this->record);
        $ticket2 = $this->peer->getTicketById($ticket->getIdentifier());
        self::assertEquals($ticket->toArray(), $ticket2->toArray());
    }

    /**
     *
     */
    public function testUpdate()
    {
        $ticket = $this->peer->createNewTicketFromImportItem($this->record);
        $id = $ticket->getIdentifier();
        $rev = $ticket->getRevision();
        self::assertTrue($this->peer->saveTicket($ticket));
        self::assertEquals($id, $ticket->getIdentifier());
        self::assertNotEquals($rev, $ticket->getRevision());
    }

    /**
     *
     */
    public function testSerializeWorkflowTicket()
    {
        $ticket = $this->peer->createNewTicketFromImportItem($this->record);
        $ticket2 = unserialize(serialize($ticket));
        self::assertEquals($ticket->toArray(), $ticket2->toArray());
    }

    /**
     *
     */
    public function testGetTicketByImportitem()
    {
        self::assertInstanceOf('WorkflowTicket', $this->peer->getTicketByImportitem($this->record));
    }

}
