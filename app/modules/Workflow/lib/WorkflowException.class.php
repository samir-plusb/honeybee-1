<?php
/**
 *
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
class WorkflowException extends Exception
{
    /**
     * thrown if Serializabale::unserialize failed
     */
    const ERROR_UNSERIALIZE = 1;
    /**
     * Loading a named workflow failed
     */
    const WORKFLOW_NOT_FOUND = 2;
    /**
     * Workflow name is invalid (RE: ^_?[a-z][a-z-0-9]+$)
     */
    const INVALID_WORKFLOW_NAME = 3;
    /**
     * invalid structure of workflow definition
     */
    const INVALID_WORKFLOW = 4;
    /**
     * workflow step not found
     */
    const STEP_MISSING = 5;
    /**
     * plugin not defined in workflow step
     */
    const PLUGIN_MISSING = 6;
    /**
     * unexpected workflow return code
     */
    const UNEXPECTED_EXIT_CODE = 7;
    /**
     * maximum number of workflow step executions exceeded
     */
    const MAX_STEP_EXECUTIONS_EXCEEDED = 8;

}