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
        $this->setActionAttributes($parameters);
        $itemFinder = $this->getAttribute('finder');
        $result = $this->loadItems();

        $this->setAttribute('items', $result['items']);
        $this->setAttribute('totalCount', $result['totalCount']);

        return 'Success';
    }

    protected function setActionAttributes(AgaviRequestDataHolder $parameters)
    {
        $itemFinder = $this->getContext()->getModel('ItemFinder');
        $this->setAttribute('offset', $parameters->getParameter('offset', self::DEFAULT_OFFSET));
        $this->setAttribute('limit', $parameters->getParameter('limit', self::DEFAULT_LIMIT));
        $sorting = array(
            'direction' => $parameters->getParameter('sorting[direction]', self::DEFAULT_SORT_DIRECTION),
            'field'     => $parameters->getParameter('sorting[field]', self::DEFAULT_SORT_FIELD)
        );
        $this->setAttribute('sorting', $sorting);
        $this->setAttribute('finder', $itemFinder);
        $searchPhrase = $parameters->getParameter('search_phrase');
        if (! empty($searchPhrase))
        {
            $this->setAttribute('search_phrase', $searchPhrase);
        }
    }

    protected function loadItems()
    {
        $itemFinder = $this->getAttribute('finder');
        $limit = $this->getAttribute('limit');
        $offset = $this->getAttribute('offset');
        $sorting = $this->getAttribute('sorting');

        if ($this->hasAttribute('search_phrase'))
        {
            return $itemFinder->search(
                strtolower($this->getAttribute('search_phrase')),
                $sorting['field'],
                $sorting['direction'],
                $offset,
                $limit
            );
        }

        return $itemFinder->fetchAll(
            $sorting['field'],
            $sorting['direction'],
            $offset,
            $limit
        );
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