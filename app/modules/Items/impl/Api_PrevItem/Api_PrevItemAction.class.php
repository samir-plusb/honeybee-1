<?php

/**
 * The Items_Api_PrevItemAction is repsonseable handling the retrieval of the previous editable item
 * relative to a given current item.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Mvc
 */
class Items_Api_PrevItemAction extends ItemsListBaseAction
{
    /**
     * Execute the read logic for this action, hence find/load the previous item.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     */
    public function executeRead(AgaviRequestDataHolder $parameters)
    {
        $this->setActionAttributes($parameters);
        $item = $this->findPreviousItem();
        if ($item)
        {
            $supervisor = Workflow_SupervisorModel::getInstance();
            $ticketPeer = $supervisor->getTicketPeer();
            $this->setAttribute('ticket', $ticketPeer->getTicketById($item->getTicketId()));
        }
        $this->setAttribute('item', $item);
        return 'Success';
    }

    /**
     * Try and find the previous item relative to our current item.
     *
     * This is done in a sliding window manner,
     * recursively calling our findPreviousItem method until we hit either beginning of our range.
     * As mentioned inside the ItemFinder, this method is a hack until product management has
     * distinguished the behaviour we really want.
     * <b>10.02.2012 thorsten schmitt-rink - works for the moment, get rid of as soon as possible.</b>
     *
     * @return IWorkflowItem
     */
    protected function findPreviousItem()
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
            return $this->findPreviousItem();
        }
        $itemCount = count($items);
        $lastItem = $items[$itemCount - 1];
        $curItem = $this->getAttribute('cur_item');
        if ($curItem === $lastItem->getIdentifier() && 0 === $offset)
        {
            return $items[$itemCount - 2];
        }

        for ($i = 1; $i < count($items); $i++)
        {
            $item = $items[$i];
            if ($item->getIdentifier() === $curItem)
            {
                return $items[$i-1];
            }
        }

        if (0 === $offset)
        {
            return NULL;
        }

        $offset -= $limit;
        $this->setAttribute('offset', (0 > $offset) ? 0 : $offset);
        return $this->findPreviousItem();
    }
}

?>
