<?php

/**
 * This IWorkflowPluginResult interface defines the requirements of an workflow plugin.
 *
 * @author tay
 * @version $Id: IWorkflowPlugin.iface.php 690 2012-01-13 02:50:14Z tschmitt $
 * @package Workflow
 * @subpackage Plugin
 */
interface IWorkflowPluginResult
{
    /**
     * plugin has an internal error. A message is available.
     */
    const STATE_ERROR = 0;

    /**
     * plugin has successfully processed
     */
    const STATE_OK = 1;

    /**
     * The ticket should be suspended until time
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
}

?>
