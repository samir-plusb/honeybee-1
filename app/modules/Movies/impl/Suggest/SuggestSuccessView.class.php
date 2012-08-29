<?php

/**
 * The Movies_Suggest_SuggestSuccessView class handles the presentation logic for our
 * Movies/Suggest actions's success data.
 *
 * @version         $Id: Movies_Suggest_SuggestSuccessView.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Movies
 * @subpackage      Mvc
 */
class Movies_Suggest_SuggestSuccessView extends MoviesBaseView
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
            'state'     => 'ok',
            'messages' => array(),
            'data' => $this->getAttribute('state')->getData()
        );
        $this->getResponse()->setContent(htmlspecialchars_decode(json_encode($data)));
    }
}

?>
