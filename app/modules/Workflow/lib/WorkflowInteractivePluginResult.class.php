<?php

/**
 * holds the result of a interactive plugin process method call
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 * @since 28.10.2011
 *
 */
class WorkflowInteractivePluginResult extends WorkflowPluginResult
{
    /**
     * plugin action response to interact with user
     *
     * @var AgaviResponse
     */
    private $response;

    /**
     *
     * @see WorrkflowPluginResult::__construct
     *
     * @param AgaviExecutionContainer $container
     * @param int $state
     * @param int $gate
     * @param string $message
     */
    public function __construct(AgaviResponse $response, $state, $gate = self::GATE_NONE, $message = NULL)
    {
        parent::__construct($state, $gate, $message);
        $this->response = $response;
    }


    /**
     * return response for interacting with user
     *
     * @return AgaviResponse
     */
    public function getResponse()
    {
        return $this->response;
    }
}