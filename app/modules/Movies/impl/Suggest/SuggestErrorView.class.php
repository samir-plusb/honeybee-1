<?php

/**
 * The Movies_Suggest_SuggestErrorView class handles the presentation logic for our
 * Movies/Suggest actions's error data.
 *
 * @version         $Id: Movies_Suggest_SuggestErrorView.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Movies
 * @subpackage      Mvc
 */
class Movies_Suggest_SuggestErrorView extends MoviesBaseView
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
