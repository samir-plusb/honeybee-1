<?php

/**
 * The Common_PaginateAction is repsonseable for rendering given data list style.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Common
 * @subpackage      Mvc
 */
class Common_PaginateAction extends CommonBaseAction
{
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
        $listConfig = $parameters->getParameter('config');
        $listState = $parameters->getParameter('state');

        $limit = $listState->getLimit();
        $routeName = $listConfig->getRouteName();
        $currentOffset = $listState->getOffset();
        $totalCount = $listState->getTotalCount();
        $currentPage = (int)floor($currentOffset / $limit);
        $lastPage = (int)ceil($totalCount / $limit) - 1;
        $totalPages = (int)ceil($totalCount / $limit);
        
        $pagingRange = $listConfig->getPagingRange();
        if ($totalPages < 5)
        {
            $pagingRange = 0;
        }
        elseif ($pagingRange > $totalPages - 4)
        {
            $pagingRange = $totalPages - 4;
        }

        $attributes = array(
            'last_page' => $lastPage,
            'current_page' => $currentPage,
            'has_previous' => (0 < $currentPage),
            'has_next' => ($currentPage < $lastPage),
            'first_page_reached' => (0 === $currentPage),
            'last_page_reached' => ($currentPage === $lastPage),
            'paging_range' => $pagingRange,
            'limit' => $limit,
            'offset' => $currentOffset,
            'total_count' => $totalCount,
            'total_pages' => $totalPages,
            'route_name' => $routeName,
            'sorting' => array(
                'direction' => $listState->getSortDirection(),
                'field' => $listState->getSortField()
            )
        );
        if ($listState->hasSearch())
        {
            $attributes['search'] = $listState->getSearch();
        }
        else if ($listState->hasFilter())
        {
            $attributes['filter'] = $listState->getFilter();
        }
        foreach ($attributes as $attr => $val)
        {
            $this->setAttribute($attr, $val);
        }

        return 'Success';
    }

    public function handleReadError(AgaviRequestDataHolder $parameters)
    {
        var_dump($this->getContainer()->getValidationManager()->getErrorMessages());exit;
    }
}

?>