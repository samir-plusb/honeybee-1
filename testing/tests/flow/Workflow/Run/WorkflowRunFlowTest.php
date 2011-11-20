<?php

/**
 *
 *
 * @author tay
 * @version $Id:$
 * @since 31.10.2011
 *
 * @agaviRoutingInput workflow.run
 * @agaviRequestMethod Read
 * @agaviIsolationDefaultContext console
 */
class WorkflowRunFlowTest extends AgaviFlowTestCase
{

	const ITEM = '{"_id":"33112544","_rev":"1-66239a2f4c4d9a47e1b622484f308429","identifier":"33112544", "importItem": { "parentIdentifier": "33112544", "source":"dpa - Deutsche Presse-Agentur GmbH","timestamp":"2011-10-20T15:15:01+0200","title":"Merkel f\u00fcr Bund-L\u00e4nder-Zusammenarbeit bei Integration","content":"Berlin (dpa/bb) - Der Bund will beim Thema Integration in der Schule eng mit den L\u00e4ndern zusammenarbeiten. \u00abWir gehen hier nicht nach Zust\u00e4ndigkeiten\u00bb, sagte Bundeskanzlerin Angela Merkel (CDU) am Donnerstag bei einem Besuch der Erika-Mann-Grundschule in Berlin-Wedding. \u00abWir wollen Hand in Hand arbeiten, damit jedes Kind in Deutschland einen Ausbildungsplatz bekommt.\u00bb Am Nachmittag wollte Merkel bei der Kultusministerkonferenz dabei sein, die sich mit der Integrationspolitik besch\u00e4ftigt. Die Erika-Mann-Schule hat einen hohen Anteil an Sch\u00fclern mit ausl\u00e4ndischen Wurzeln und gilt als Vorzeigeschule.","category":"/regioline/berlinbrandenburg/","media":[],"geoData":[],"subtitle":"","abstract":null,"keywords":["Bildung","Kultusministerkonferenz"],"copyright":"dpa-info.com GmbH","release":null,"expire":null,"table":[],"links":["<a href=\"http://dpaq.de/m4Bjt\">Erika-Mann-Grundschule</a>","<a href=\"http://dpaq.de/amPDH\">Pressemitteilung Senatsverwaltung</a>"] } }';

	/**
     *
     * @var IWorkflowItem
     */
    protected $item;

    /**
     *
     * @var WorkflowTicket
     */
    protected $ticket;

    /**
     *
     * @var Workflow_SupervisorModel
     */
    protected $supervisor;

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        $_SERVER['argv'] = array($this->getDispatchScriptName(), $this->getRoutingInput());
        parent::__construct($name, $data, $dataName);
    }


    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        $workflowSetup = new WorkflowModuleSetup();
        $workflowSetup->setup(TRUE);
        
        $this->supervisor = Workflow_SupervisorModel::getInstance();
        $this->item = new WorkflowItem(json_decode(self::ITEM,TRUE));
        $peer = $this->supervisor->getItemPeer();
        $peer->storeItem($this->item);

        $ticket = new WorkflowTicket();
        $ticket->setWorkflowItem($this->item);
        $ticket->setWorkflow('TestInteractive');
        $this->supervisor->getTicketPeer()->saveTicket($ticket);
        $this->ticket = $ticket;

        $_SERVER['SERVER_SOFTWARE'] = 'Apache/2';
    }


    /**
     *
     */
    public function testInteractiveWorkflowPrompt()
    {
        $this->dispatch(array('ticket' => $this->ticket->getIdentifier()));
        self::assertStringStartsWith('Choose a gate', $this->response->getContent());
    }

    /**
     * @agaviRequestMethod Write
     */
    public function testInteractiveWorkflowPost()
    {
        $args = array(
            'ticket' => $this->ticket->getIdentifier(),
            'gate' => 1
        );
        $this->dispatch($args);
        self::assertStringStartsWith('Gate choosen', $this->response->getContent());
    }

}