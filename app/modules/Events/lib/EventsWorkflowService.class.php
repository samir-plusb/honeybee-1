<?php

/**
 *
 * @version $Id: EventsWorkflowService.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Events
 */
class EventsWorkflowService extends BaseWorkflowService
{
    const ITEM_IMPLEMENTOR = 'EventsWorkflowItem';

    private static $instance;

    public static function getInstance()
    {
        if (NULL === self::$instance)
        {
            self::$instance = new EventsWorkflowService('Events');
        }
        return self::$instance;
    }

    protected function getWorkflowItemImplementor()
    {
        return self::ITEM_IMPLEMENTOR;
    }
}

?>
