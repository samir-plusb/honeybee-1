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
    const NEWS_WORKFLOW_NAME = 'news';

    const DEFAULT_LIMIT = 30;

    const DEFAULT_OFFSET = 0;

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
        $workflowTicketFinder = $this->getContext()->getModel('TicketFinder');
        $limit = $parameters->getParameter('limit', self::DEFAULT_LIMIT);
        $offset = $parameters->getParameter('offset', self::DEFAULT_OFFSET);
        $searchPhrase = $parameters->getParameter('search_phrase', NULL);
        $result = array(
            'tickets'    => array(),
            'totalCount' => 0
        );

        if (! empty($searchPhrase))
        {
            $this->setAttribute('search_phrase', $searchPhrase);
            $result = $workflowTicketFinder->search(
                strtolower($searchPhrase),
                $offset,
                $limit
            );
        }
        else
        {
            $result = $workflowTicketFinder->fetchAll(
                self::NEWS_WORKFLOW_NAME,
                $offset,
                $limit
            );
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

        return 'Error';
    }

    /**
     * (non-PHPdoc)
     * @see AgaviAction::isSecure()
     */
    public function isSecure()
    {
        return TRUE;
    }
}

?>