<?php

/**
 * The Items_ListAction is repsonseable for loading the news items list thereby exposing several parameters
 * to the outside world in order to provide typical list behaviour such as limit/offset based pagination,
 * search and sorting.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Mvc
 */
class Items_ListAction extends ItemsListBaseAction
{
    /**
     * Execute the read logic for this action, hence load our news items.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     */
    public function executeRead(AgaviRequestDataHolder $parameters)
    {
        $this->setActionAttributes($parameters);
        $result = $this->loadItems();
        $this->setAttribute('items', $result['items']);
        $this->setAttribute('totalCount', $result['totalCount']);

        return 'Success';
    }
}

?>