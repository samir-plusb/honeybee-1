<?php

use Honeybee\Core\Workflow\Exception;

/**
 * The IWorkflowPluginResult provides access to the result of an IWorkflowPlugin::process method's invocation.
 * It exposes data concerning the state, gate and feedback message of the plugin execution.
 * A IWorkflowPluginResult can be frozen by a call to it's freeze method.
 * Once frozen a plugin result may not be modified anymore and attempts to do so shall lead to Exceptions.
 *
 * @author tay
 * @package Workflow
 * @subpackage Plugin
 */
interface IWorkflowPluginResult extends IDataObject
{
    /**
     * Plugin has an internal error.
     * An (error)message must be available.
     */
    const STATE_ERROR = 0;

    /**
     * The plugin has successfully been processed.
     */
    const STATE_OK = 1;

    /**
     * The ticket should be suspended until a given time.
     */
    const STATE_WAIT_UNTIL = 2;

    /**
     * The ticket should be suspended until interactive request
     */
    const STATE_EXPECT_INPUT = 3;

    /**
     * The requested plugin execution has been denied, due to insufficient privleges.
     */
    const STATE_NOT_ALLOWED = 4;

    /**
     * Freeze the current plugin result.
     */
    public function freeze();

    /**
     * Return the plugin result's state.
     *
     * @return int
     */
    public function getState();

    /**
     * Set the plugin result's state.
     *
     * @param string $state Restricted to one of the STATE_* constants.
     *
     * @throws Exception If the result has been frozen.
     */
    public function setState($state);

    /**
     * Return the plugin result's gate.
     *
     * @return string
     */
    public function getGate();

    /**
     * Set the plugin result's gate.
     *
     * @param string $gate
     *
     * @throws Exception If the result has been frozen.
     */
    public function setGate($gate);

    /**
     * Return the plugin result's message.
     *
     * @return string
     */
    public function getMessage();

    /**
     * Set the plugin result's message.
     *
     * @param string $message
     *
     * @throws Exception If the result has been frozen.
     */
    public function setMessage($message);
}

?>
