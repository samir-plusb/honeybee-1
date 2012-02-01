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

    const DEFAULT_SORT_FIELD = 'timestamp';

    const DEFAULT_SORT_DIRECTION = 'desc';

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
        $itemFinder = $this->getContext()->getModel('ItemFinder');
        $limit = $parameters->getParameter('limit', self::DEFAULT_LIMIT);
        $offset = $parameters->getParameter('offset', self::DEFAULT_OFFSET);
        $searchPhrase = $parameters->getParameter('search_phrase');
        $sorting = array(
            'direction' => $parameters->getParameter('sorting[direction]', self::DEFAULT_SORT_DIRECTION),
            'field'     => $parameters->getParameter('sorting[field]', self::DEFAULT_SORT_FIELD)
        );

        $result = array(
            'tickets'    => array(),
            'totalCount' => 0
        );

        if (! empty($searchPhrase))
        {
            $this->setAttribute('search_phrase', $searchPhrase);
            $result = $itemFinder->search(
                strtolower($searchPhrase),
                $sorting['field'],
                $sorting['direction'],
                $offset,
                $limit
            );
        }
        else
        {
            $result = $itemFinder->fetchAll(
                $sorting['field'],
                $sorting['direction'],
                $offset,
                $limit
            );
        }

        $this->setAttribute('offset', $offset);
        $this->setAttribute('limit', $limit);
        $this->setAttribute('items', $result['items']);
        $this->setAttribute('totalCount', $result['totalCount']);
        $this->setAttribute('sorting', $sorting);

        return 'Success';
    }

    public function handleError(AgaviRequestDataHolder $parameters)
    {
        parent::handleError($parameters);
        $errors = array();

        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $errMsg)
        {
            $errors[implode(', ', array_values($errMsg['errors']))] = $errMsg['message'];
        }
        $this->setAttribute('error_messages', $errors);
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