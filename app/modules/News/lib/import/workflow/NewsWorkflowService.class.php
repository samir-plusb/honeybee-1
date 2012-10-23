<?php

/**
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package News
 * @subpackage Import/Workflow
 */
class NewsWorkflowService extends BaseWorkflowService
{
    const ITEM_IMPLEMENTOR = 'NewsWorkflowItem';

    private static $instance;

    /**
     *
     * @return NewsWorkflowService
     */
    public static function getInstance()
    {
        if (NULL === self::$instance)
        {
            self::$instance = new NewsWorkflowService('news');
        }
        return self::$instance;
    }

    protected function getWorkflowItemImplementor()
    {
        return self::ITEM_IMPLEMENTOR;
    }
}

?>
