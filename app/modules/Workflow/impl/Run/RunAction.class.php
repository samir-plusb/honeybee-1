<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Workflow
 */
class Workflow_RunAction extends ProjectWorkflowBaseAction
{
    /**
     * (non-PHPdoc)
     * @see AgaviAction::getDefaultViewName()
     */
    public function execute(AgaviParameterHolder $rd)
    {
        return 'Input';
    }
}