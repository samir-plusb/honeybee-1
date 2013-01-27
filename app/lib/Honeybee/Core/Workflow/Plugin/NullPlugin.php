<?php

namespace Honeybee\Core\Workflow\Plugin;

use Honeybee\Core\Workflow\Plugin;

/**
 * This is the simplest plugin which does nothing than returning a success result
 * pointing to the promote gate.
 *
 * @author tay
 */
class NullPlugin extends Plugin\BasePlugin
{
    /**
     * Run our buisiness.
     * As a null plugin we always return a success result forwarding to our promote gate.
     *
     * @return Plugin\Result
     */
    protected function doProcess()
    {
        $result = new Plugin\Result();
        $result->setState(Plugin\Result::STATE_EXPECT_INPUT);
        $result->freeze();

        return $result;
    }
}
