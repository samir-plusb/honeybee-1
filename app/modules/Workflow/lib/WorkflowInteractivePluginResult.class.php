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
     * forward container to interact with user
     *
     * @var AgaviExecutionContainer
     */
    private $container;

    /**
     *
     * @see WorrkflowPluginResult::__construct
     *
     * @param AgaviExecutionContainer $container
     * @param int $state
     * @param int $gate
     * @param string $message
     */
    public function __construct(AgaviExecutionContainer $container, $state, $gate = self::GATE_NONE, $message = NULL)
    {
        parent::__construct($state, $gate, $message);
        $this->container = $container;
    }


    /**
     * return forward container for interacting with user
     *
     * @return AgaviExecutionContainer
     */
    public function getContainer()
    {
        return $this->getContainer();
    }
}