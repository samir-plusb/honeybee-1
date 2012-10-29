<?php

/**
 * The ProjectExecutionFilter class registers view executions for the ProjectScriptFilter.
 * We will probally switch to using pulq pretty soon.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Project
 * @subpackage      Agavi/Filter
 */
class ProjectExecutionFilter extends PhpDebugToolbarAgaviExecutionFilter
{
    protected function executeView(AgaviExecutionContainer $container)
    {
        $viewResult = parent::executeView($container);

        ProjectResourceFilter::addModule(
            $container->getViewModuleName(), 
            $container->getOutputType()->getName()
        );

        return $viewResult;
    }
}
