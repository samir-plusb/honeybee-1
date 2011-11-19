<?php

/**
 * test handling of workflow items (import items)
 *
 * @package Testing
 * @subpackage Workflow
 * @author tay
 * @version $Id$
 * @since 25.10.2011
 *
 */
class WorkflowItemPeerTest extends AgaviUnitTestCase
{
    const ITEM = '{"_id":"33112544","_rev":"1-66239a2f4c4d9a47e1b622484f308429","identifier":"33112544", "importItem": { "parentIdentifier": "33112544", "source":"dpa - Deutsche Presse-Agentur GmbH","timestamp":"2011-10-20T15:15:01+0200","title":"Merkel f\u00fcr Bund-L\u00e4nder-Zusammenarbeit bei Integration","content":"Berlin (dpa/bb) - Der Bund will beim Thema Integration in der Schule eng mit den L\u00e4ndern zusammenarbeiten. \u00abWir gehen hier nicht nach Zust\u00e4ndigkeiten\u00bb, sagte Bundeskanzlerin Angela Merkel (CDU) am Donnerstag bei einem Besuch der Erika-Mann-Grundschule in Berlin-Wedding. \u00abWir wollen Hand in Hand arbeiten, damit jedes Kind in Deutschland einen Ausbildungsplatz bekommt.\u00bb Am Nachmittag wollte Merkel bei der Kultusministerkonferenz dabei sein, die sich mit der Integrationspolitik besch\u00e4ftigt. Die Erika-Mann-Schule hat einen hohen Anteil an Sch\u00fclern mit ausl\u00e4ndischen Wurzeln und gilt als Vorzeigeschule.","category":"/regioline/berlinbrandenburg/","media":[],"geoData":[],"subtitle":"","abstract":null,"keywords":["Bildung","Kultusministerkonferenz"],"copyright":"dpa-info.com GmbH","release":null,"expire":null,"table":[],"links":["<a href=\"http://dpaq.de/m4Bjt\">Erika-Mann-Grundschule</a>","<a href=\"http://dpaq.de/amPDH\">Pressemitteilung Senatsverwaltung</a>"] } }';

    /**
     *
     * @var WorkflowItem
     */
    var $item;

    /**
     *
     * @var WorkflowItemPeer
     */
    var $peer;

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
        $this->item = new WorkflowItem(json_decode(self::ITEM,TRUE));

        $setupItems = new ItemsModuleSetup();
        $setupItems->setup(TRUE);

        $this->peer = $this->supervisor->getItemPeer();
        $this->peer->storeItem($this->item);
    }


    /**
     *
     */
    public function testGetItemByIdentifier()
    {
        $item = $this->peer->getItemByIdentifier($this->item->getIdentifier());
        self::assertInstanceOf('WorkflowItem', $item);
    }


    /**
     *
     */
    public function testGetItemByIdentifierFail()
    {
        $this->setExpectedException('CouchdbClientException');
        $item = $this->peer->getItemByIdentifier('0815');
    }
}