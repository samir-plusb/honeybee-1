<?php

namespace Honeybee\Agavi\Routing;

/**
 * This routing callback may be used when overriding/redefining routes with
 * callbacks to prevent matching and usage of that route on route generation.
 */
class NoOpRoutingCallback extends \AgaviRoutingCallback
{
    /**
     * Gets executed when the route of this callback matched.
     *
     * @param array The parameters generated by this route.
     * @param \AgaviExecutionContainer The original execution container.
     *
     * @return bool false as routes with this callback should never match.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function onMatched(array &$parameters, \AgaviExecutionContainer $container) // @codingStandardsIgnoreEnd
    {
        return false;
    }

    /**
     * Gets executed when the route of this callback did not match.
     *
     * @param \AgaviExecutionContainer The original execution container.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function onNotMatched(\AgaviExecutionContainer $container) // @codingStandardsIgnoreEnd
    {
        return;
    }

    /**
     * Gets executed when the route of this callback is about to be reverse
     * generated into an URL.
     *
     * @param array The default parameters stored in the route.
     * @param array The parameters the user supplied to AgaviRouting::gen().
     * @param array The options the user supplied to AgaviRouting::gen().
     *
     * @return bool false as this route part should not be generated.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function onGenerate(array $defaultParameters, array &$userParameters, array &$userOptions) //@codingStandardsIgnoreEnd
    {
        return false;
    }
}