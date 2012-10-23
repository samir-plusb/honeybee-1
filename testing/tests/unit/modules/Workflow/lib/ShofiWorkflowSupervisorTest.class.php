<?php

/**
 * test the shofi supervisor
 *
 * @package Testing
 * @subpackage Workflow
 * @author tay
 * @version $Id$
 * @since 25.10.2011
 *
 */
class ShofiWorkflowSupervisorTest extends AgaviUnitTestCase
{
    /**
     * @var WorkflowSupervisor
     */
    protected $supervisor;

    public function setUp()
    {
        $this->supervisor = WorkflowSupervisorFactory::createByTypeKey('shofi');
        $workflowSetup = new WorkflowDatabaseSetup($this->supervisor->getDatabase());
        $workflowSetup->setup(TRUE);
    }

    public function testInstance()
    {
        self::assertInstanceOf('ShofiWorkflowSupervisor', $this->supervisor);
    }

    public function testGetDatabase()
    {
        self::assertInstanceOf('ExtendedCouchDbClient', $this->supervisor->getDatabase());
    }

    public function testGetWorkflowByName()
    {
        self::assertInstanceOf('WorkflowHandler', $this->supervisor->getWorkflowByName('_init'));
    }

    public function testGetPluginByName()
    {
        $workflow = $this->supervisor->getWorkflowByName('_init');
        self::assertInstanceOf('IWorkflowPlugin', $workflow->getPluginByName('null'));
    }

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

?>
