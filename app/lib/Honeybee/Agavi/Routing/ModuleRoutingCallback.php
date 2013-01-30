<?php

namespace Honeybee\Agavi\Routing;

class ModuleRoutingCallback extends \AgaviRoutingCallback
{
    /**
     * Routing callback that is invoked when the root we are applied to matches (routing runtime).
     *
     * @param       array $parameters
     * @param       AgaviExecutionContainer $container
     *
     * @return      boolean
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @codingStandardsIgnoreStart
     */
    public function onMatched(array &$parameters, \AgaviExecutionContainer $container) // @codingStandardsIgnoreEnd
    {
        $params = $this->route['opt']['parameters'];

        $implementor = $params['implementor'];
        if (! class_exists($implementor))
        {
            throw new \Exception("Unable to load module $implementor");
        }

        $factory = array($implementor, 'getInstance');
        if (is_callable($factory))
        {
            $module = $implementor::getInstance();
            $this->context->getRequest()->setAttribute('module', $module, 'org.honeybee.env');
        }
        else
        {
            throw new \Exception(
                "Unable to call the '$implementor' module's getInstance method."
            );
        }

        return TRUE;
    }

    /**
     * Routing callback that is invoked when the root we are applied to does not match (routing runtime).
     *
     * @param       array $parameters
     * @param       AgaviExecutionContainer $container
     *
     * @return      boolean
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @codingStandardsIgnoreStart
     */
    public function onNotMatched(\AgaviExecutionContainer $container) // @codingStandardsIgnoreEnd
    {
        return TRUE;
    }

    /**
     * Routing callback that is invoked when the root we are applied to does not match (routing runtime).
     *
     * @param       array $parameters
     * @param       AgaviExecutionContainer $container
     *
     * @return      boolean
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function onGenerate(array $defaultParameters, array &$userParameters, array &$options) //@codingStandardsIgnoreEnd
    {
        return TRUE;
    }
}
