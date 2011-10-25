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
class WorkflowSupervisorTest extends AgaviUnitTestCase
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
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        $this->supervisor = Workflow_SupervisorModel::getInstance();
        $this->record = new WorkflowItem(json_decode(self::RECORD,TRUE));
        $this->event = new ProjectEvent(BaseDataImport::EVENT_RECORD_SUCCESS, array('record' => $this->record));

        $setup = new WorkflowModuleSetup();
        $setup->setup(TRUE);

        $setupItems = new ItemsModuleSetup();
        $setupItems->setup(TRUE);

        $itemsDb = $this->supervisor->getItemPeer()->getDatabase();
        $itemsDb->storeDoc(NULL, json_decode(self::RECORD,TRUE));
    }


    /**
     *
     */
    public function testInstance()
    {
        self::assertEquals($this->supervisor, Workflow_SupervisorModel::getInstance());
    }

    /**
     *
     */
    public function testGetDatabase()
    {
        self::assertInstanceOf('ExtendedCouchDbClient', $this->supervisor->getDatabase());
    }

    /**
     *
     */
    public function testGetPluginByName()
    {
        self::assertInstanceOf('IWorkflowPlugin', $this->supervisor->getPluginByName('null'));
    }

    /**
     *
     */
    public function testGetPluginByNameFail()
    {
        try
        {
            $plugin = $this->supervisor->getPluginByName('__noplugin');
            self::assertEquals('WorkflowException', 'no exception');
        }
        catch (WorkflowException $e)
        {
            self::assertEquals(WorkflowException::PLUGIN_MISSING, $e->getCode());
        }
    }

    /**
     *
     */
    public function testCreateNewTicketFromImportItem()
    {
        $ticket = $this->supervisor->createNewTicketFromImportItem($this->record);
        self::assertInstanceOf('WorkflowTicket', $ticket);
        self::assertInstanceOf('IDataRecord', $ticket->getImportItem());
        self::assertEquals($this->record->getIdentifier(), $ticket->getImportItem()->getIdentifier());
    }

    /**
     *
     */
    public function testGetTicketByImportitem()
    {
        self::assertInstanceOf('WorkflowTicket', $this->supervisor->getTicketByImportitem($this->record));
    }

    /**
     *
     */
    public function testSerializeWorkflowTicket()
    {
        $ticket = $this->supervisor->getTicketByImportitem($this->record);
        $ticket2 = unserialize(serialize($ticket));
        self::assertEquals($ticket->toArray(), $ticket2->toArray());
    }

    /**
     *
     */
    public function testImportRecordImportedCallback()
    {
        try
        {
            $this->supervisor->importRecordImportedCallback($this->event);
            self::assertEquals('WorkflowException', 'no exception');
        }
        catch (WorkflowException $e)
        {
            self::assertEquals(WorkflowException::WORKFLOW_NOT_FOUND, $e->getCode());
        }
    }

}
