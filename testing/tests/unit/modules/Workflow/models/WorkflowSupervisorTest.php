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

        $setup = new WorkflowModuleSetup();
        $setup->setup(TRUE);

        $setupItems = new ItemsModuleSetup();
        $setupItems->setup(TRUE);
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
    public function testGetWorkflowByName()
    {
        self::assertInstanceOf('WorkflowHandler', $this->supervisor->getWorkflowByName('_init'));
    }


    /**
     *
     */
    public function testGetPluginByName()
    {
        $workflow = $this->supervisor->getWorkflowByName('_init');
        self::assertInstanceOf('IWorkflowPlugin', $workflow->getPluginByName('null'));
    }

    /**
     *
     */
    public function testGetPluginByNameFail()
    {
        try
        {
            $workflow = $this->supervisor->getWorkflowByName('_init');
            $plugin = $workflow->getPluginByName('__noplugin');
            self::assertEquals('WorkflowException', 'no exception');
        }
        catch (WorkflowException $e)
        {
            self::assertEquals(WorkflowException::PLUGIN_MISSING, $e->getCode());
        }
    }
}
