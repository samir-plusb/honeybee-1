<?php

use Honeybee\Core\Workflow\Process;

/**
 * IWorkflowPlugin define the smallest part within the workflow architecture.
 * They reflect work that is done in the context of a single "workflow step"
 * and may provide a arbitary number of named "gates",
 * which are transistions to other steps, workflows or define a workflow end.
 *
 * @author tay
 * @package Workflow
 * @subpackage Plugin
 */
interface IWorkflowPlugin
{
    /**
     * initialize plugin
     *
     * @param Process $resource workflow state
     * @param string $stepName
     *
     * @return IWorkflowPlugin return $this for fluid interface
     */
    public function initialize(Process $workflow, $stepName);

    /**
     * Return the plugin's unique identifier.
     * Should be human readable and use underscore notation like for example: delete_newsitem
     *
     * @return string
     */
    public function getPluginId();

    /**
     * Return the plugin's gates.
     *
     * @return array
     */
    public function getGates();

    /**
     * Return the plugin's parameters.
     *
     * @return array
     */
    public function getParameters();

    /**
     * process the ticket
     *
     * @return WorkflowPluginResult
     */
    public function process();

    /**
     * Tells whether the plugin is an interactive plugin or not.
     *
     * @return boolean TRUE if plugin will interact with user
     */
    public function isInteractive();

    /**
     * Invoked when the plugin is left by a resource through one of it's gates.
     */
    public function onResourceLeaving($gateName);

    /**
     * Invoked when the plugin is entered by a resourced.
     */
    public function onResourceEntered($prevStepName);
}
