<?php

/**
 * The Items_ListAction is repsonseable for loading our import items for display.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Mvc
 */
class Items_ListAction extends ItemsBaseAction
{
    const COUCHDB_DATABASE = 'midas_import';

    /**
     * Execute the read logic for this action, hence prompt for an asset.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeRead(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $result = array(
            'tickets' => array(),
            'totalCount' => 0
        );
        $ticketFinder = $this->getContext()->getModel('TicketFinder');
        $limit = $parameters->getParameter('limit', 30);
        $offset = $parameters->getParameter('offset', 0);
        $searchPhrase = $parameters->getParameter('search_phrase', NULL);
        
        if (! empty($searchPhrase))
        {
            $this->setAttribute('search_phrase', $searchPhrase);
            $result = $ticketFinder->search(
            strtolower($searchPhrase),
                $offset,
                $limit
            );
        }
        else
        {
            $result = $ticketFinder->fetchAll($offset, $limit);
        }
        
        $this->setAttribute('offset', $offset);
        $this->setAttribute('limit', $limit);
        $this->setAttribute('tickets', $result['tickets']);
        $this->setAttribute('totalCount', $result['totalCount']);

        return 'Success';
    }

    public function handleError(AgaviRequestDataHolder $parameters)
    {
        parent::handleError($parameters);
        
        var_dump($this->getContainer()->getValidationManager()->getErrorMessages());exit;
    }

}

?>