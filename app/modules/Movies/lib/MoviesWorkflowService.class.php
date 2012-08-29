<?php

/**
 *
 * @version $Id: MoviesWorkflowService.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Movies
 */
class MoviesWorkflowService extends BaseWorkflowService
{
    const ITEM_IMPLEMENTOR = 'MoviesWorkflowItem';

    private static $instance;

    public static function getInstance()
    {
        if (NULL === self::$instance)
        {
            self::$instance = new MoviesWorkflowService('movies');
        }
        return self::$instance;
    }

    protected function getWorkflowItemImplementor()
    {
        return self::ITEM_IMPLEMENTOR;
    }
}

?>
