<?php

/**
 * The Events_Suggest_SuggestErrorView class handles the presentation logic for our
 * Events/Suggest actions's error data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Events
 * @subpackage      Mvc
 */
class Events_Suggest_SuggestErrorView extends EventsBaseView
{
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
            'state'     => 'error',
            'errors' => $this->getAttribute('error_messages'),
            'data' => array()
        );
        $this->getResponse()->setContent(json_encode($data));
    }
}

?>
