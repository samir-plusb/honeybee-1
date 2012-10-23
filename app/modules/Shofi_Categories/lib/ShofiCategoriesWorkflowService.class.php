<?php

/**
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Shofi_Categories
 */
class ShofiCategoriesWorkflowService extends BaseWorkflowService
{
    const ITEM_IMPLEMENTOR = 'ShofiCategoriesWorkflowItem';

    private static $instance;

    public static function getInstance()
    {
        if (NULL === self::$instance)
        {
            self::$instance = new ShofiCategoriesWorkflowService('shofi_categories');
        }
        return self::$instance;
    }

    protected function getWorkflowItemImplementor()
    {
        return self::ITEM_IMPLEMENTOR;
    }
}
