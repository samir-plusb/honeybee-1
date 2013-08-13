<?php

namespace Honeybee\Agavi\Filter;

class DebugExecutionFilter extends \AgaviExecutionFilter
{
    protected function executeView(\AgaviExecutionContainer $container)
    {
        $viewResult = parent::executeView($container);

        ResourceFilter::addModule(
            $container->getViewModuleName(),
            $container->getOutputType()->getName()
        );

        return $viewResult;
    }
}
