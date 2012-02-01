<?php

/**
 * The Items_Api_NextItemAction is repsonseable handling the retrieval of the next editable item
 * relative to a given current item.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Mvc
 */
class Items_Api_NextItemAction extends ItemsBaseAction
{
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
        $item = $this->findNextItem();

        if ($item)
        {
            $supervisor = Workflow_SupervisorModel::getInstance();
            $ticketPeer = $supervisor->getTicketPeer();
            $this->setAttribute('ticket', $ticketPeer->getTicketById($item->getTicketId()));
        }

        $this->setAttribute('item', $item);

        return 'Success';
    }

    protected function findNextItem()
    {
        $result = $this->loadItems();
        $items = $result['items'];

        $offset = $this->getAttribute('offset');
        $limit = $this->getAttribute('limit');

        if (1 >= count($items))
        {
            if ($offset <= $limit) // first page, no more left to search
            {
                return NULL;
            }
            $offset -= $limit;
            if ($offset >= $result['totalCount'])
            {
                $offset = $result['totalCount'] - $limit;
            }

            $this->setAttribute('offset', (0 > $offset) ? 0 : $offset);
            return $this->findNextItem();
        }

        $firstItem = $items[0];
        $curItem = $this->getAttribute('cur_item');
        if ($curItem === $firstItem->getIdentifier() && 0 === $offset)
        {
            return $items[1];
        }

        for ($i = 1; $i < count($items) - 1; $i++)
        {
            $item = $items[$i];
            if ($item->getIdentifier() === $curItem)
            {
                return $items[$i+1];
            }
        }

        if (0 === $offset)
        {
            return NULL;
        }

        $offset -= $limit;
        $offset = 0 > $offset ? 0 : $offset;
        $this->setAttribute('offset', $offset);
        return $this->findNextItem();
    }

    protected function loadItems()
    {
        $itemFinder = $this->getAttribute('finder');

        $limit = $this->getAttribute('limit');
        $offset = $this->getAttribute('offset');
        $sorting = $this->getAttribute('sorting');
        $result = array(
            'tickets'    => array(),
            'totalCount' => 0
        );

        if ($this->hasAttribute('search_phrase'))
        {
            $result = $itemFinder->search(
                strtolower($this->getAttribute('search_phrase')),
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
        return $result;
    }

    protected function setActionAttributes(AgaviRequestDataHolder $parameters)
    {
        $itemFinder = $this->getContext()->getModel('ItemFinder');
        $itemFinder->enableEditFilter($parameters->getParameter('cur_item'));

        $limit = $parameters->getParameter('limit', self::DEFAULT_LIMIT);
        $offset = $parameters->getParameter('offset', self::DEFAULT_OFFSET);
        $searchPhrase = $parameters->getParameter('search_phrase');
        $sorting = array(
            'direction' => $parameters->getParameter('sorting[direction]', self::DEFAULT_SORT_DIRECTION),
            'field'     => $parameters->getParameter('sorting[field]', self::DEFAULT_SORT_FIELD)
        );

        if ($searchPhrase)
        {
            $this->setAttribute('search_phrase', $searchPhrase);
        }

        $this->setAttribute('offset', $offset);
        $this->setAttribute('limit', $limit);
        $this->setAttribute('sorting', $sorting);
        $this->setAttribute('finder', $itemFinder);
        $this->setAttribute('cur_item', $parameters->getParameter('cur_item'));
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
