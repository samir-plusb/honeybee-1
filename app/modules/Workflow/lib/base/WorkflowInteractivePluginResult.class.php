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
