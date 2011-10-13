<?php
/**
 * test handling workflow definition handling
 *
 * @package Testing
 * @subpackage Workflow
 * @author tay
 * @version $Id$
 * @since 13.10.2011
 *
 */
class WorkflowConfigSyntaxTest extends AgaviUnitTestCase
{
    /**
     *
     * @var Workflow_SupervisorModel
     */
    protected $supervisor;

    public function setUp()
    {
        $this->supervisor = Workflow_SupervisorModel::getInstance();
    }


    /**
     * check for correct answer if workflow does not exists
     */
    public function testMissingWorkflow()
    {
        $this->setExpectedException('WorkflowException');
        $this->supervisor->getWorkflowByName('__not_existing_workflow');
    }


    /**
     * check for defined standard workflow
     */
    public function testForInitWorkflow()
    {
        return $this->testWorkflowConfig('_init');
    }


    /**
     * check syntax of all existing workflows
     *
     * @param string $name name of workflow
     * @dataProvider getAllWorkflowNames
     */
    public function testWorkflowConfig($name)
    {
        $workflow = $this->supervisor->getWorkflowByName($name);
        self::assertInstanceOf('WorkflowHandler', $workflow);
    }


    /**
     * data provider method for testWorkflowConfig()
     */
    public function getAllWorkflowNames()
    {
        $pattern = AgaviConfig::get('core.app_dir').
            '/'.Workflow_SupervisorModel::WORKFLOW_CONFIG_DIR .
            '*.workflow.xml';
        $files = glob($pattern);
        $parameters = array();
        foreach ($files as $file)
        {
            if (preg_match('#([^/]+)\.workflow.xml$#', $file, $m))
            {
                $parameters[] = array('name' => $m[1]);
            }
        }
        return $parameters;
    }
}