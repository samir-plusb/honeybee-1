<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Workflow
 */
class Workflow_RunAction extends ProjectBaseAction
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