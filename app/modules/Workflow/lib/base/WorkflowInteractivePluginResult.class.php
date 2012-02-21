<?php

/**
 * holds the result of a interactive plugin process method call
 *
 * @author tay
 * @version $Id$
 * @package Workflow
 * @subpackage Plugin
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
     * return response for interacting with user
     *
     * @return AgaviResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(AgaviResponse $response)
    {
        $this->verifyMutability();
        $this->response = $response;
    }
}

?>
