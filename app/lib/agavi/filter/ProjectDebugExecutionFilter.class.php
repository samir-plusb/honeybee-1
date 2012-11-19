<?php

class ProjectDebugExecutionFilter extends PhpDebugToolbarAgaviExecutionFilter
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
