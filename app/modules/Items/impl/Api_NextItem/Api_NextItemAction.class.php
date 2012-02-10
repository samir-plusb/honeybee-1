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
class Items_Api_NextItemAction extends ItemsListBaseAction
{
    /**
     * Execute the read logic for this action, hence find/load the next item.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     */
    public function executeRead(AgaviRequestDataHolder $parameters)
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

    /**
     * Try and find the next item relative to our current item.
     *
     * This is done in a sliding window manner,
     * recursively calling our findNextItem method until we hit either beginning of our range.
     * As mentioned inside the ItemFinder, this method is a hack until product management has
     * distinguished the behaviour we really want.
     * <b>10.02.2012 thorsten schmitt-rink - works for the moment, get rid of as soon as possible.</b>
     *
     * @return IWorkflowItem
     */
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
}

?>
