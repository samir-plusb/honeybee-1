<?php

/**
 * The Events_List_ListErrorView class handles the presentation logic for our
 * Events/List actions's error data.
 *
 * @version         $Id: Events_List_ListErrorView.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Events
 * @subpackage      Mvc
 */
class Events_List_ListErrorView extends EventsBaseView
{
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);
    }

    /**
     * Handle presentation logic for json.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $data = array(
            'ok'     => FALSE,
            'errors' => $this->getErrorMessages()
        );
        $this->getResponse()->setContent(json_encode($data));
    }
}

?>
