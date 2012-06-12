<?php

/**
 *
 * @version $Id: ShofiVerticalsWorkflowService.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Shofi_Verticals
 */
class ShofiVerticalsWorkflowService extends BaseWorkflowService
{
    const ITEM_IMPLEMENTOR = 'ShofiVerticalsWorkflowItem';

    private static $instance;

    public static function getInstance()
    {
        if (NULL === self::$instance)
        {
            self::$instance = new ShofiVerticalsWorkflowService('shofi_verticals');
        }
        return self::$instance;
    }

    protected function getWorkflowItemImplementor()
    {
        return self::ITEM_IMPLEMENTOR;
    }
}

?>
