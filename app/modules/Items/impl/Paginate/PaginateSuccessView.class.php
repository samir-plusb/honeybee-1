<?php

/**
 * The Items_Paginate_PaginateSuccessView class handles Items/Paginate success data presentation.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Mvc
 */
class Items_Paginate_PaginateSuccessView extends ItemsBaseView
{

    /**
     * Handle presentation logic for the web  (html).
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeHtml(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->setupHtml($parameters);
        
        $limit = $parameters->getParameter('limit');
        $currentOffset = $parameters->getParameter('offset');
        $totalCount = $parameters->getParameter('total_count');
        $currentPage = (int)floor($currentOffset / $limit);
        $lastPage = (int)ceil($totalCount / $limit) - 1;
        $totalPages = (int)ceil($totalCount / $limit);
        $pagingRange = $parameters->getParameter('paging_range');
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
            'total_pages' => $totalPages
        );
        
        if ($parameters->hasParameter('search_phrase'))
        {
            $attributes['search_phrase'] = $parameters->getParameter('search_phrase');
        }
        
        foreach ($attributes as $attr => $val)
        {
            $this->setAttribute($attr, $val);
        }
        $this->setAttribute('links', $this->generatePagingLinks());
    }
    
    protected function generatePagingLinks()
    {
        $routing = $this->getContext()->getRouting();
        $limit = $this->getAttribute('limit');
        $currentOffset = $this->getAttribute('offset');
        $totalCount = $this->getAttribute('total_count');
        $currentPage = (int) floor($currentOffset / $limit);
        $lastPage = $this->getAttribute('last_page');
        $searchPhrase = $this->getAttribute('search_phrase', FALSE);
        $pageLinksData = array(
            'first_page' => array(
                'limit' => $limit,
                'offset' => 0
            ),
            'last_page' => array(
                'limit' => $limit,
                'offset' => $lastPage * $limit
            ),
            'second_page' => array(
                'limit' => $limit,
                'offset' => $limit
            ),
            'second_last_page' => array(
                'limit' => $limit,
                'offset' => ($lastPage - 1) * $limit
            ),
            'previous_page' => array(
                'limit' => $limit,
                'offset' => ($currentPage - 1) * $limit
            ),
            'next_page' => array(
                'limit' => $limit,
                'offset' => ($currentPage + 1) * $limit
            )
        );
        
        $urls = array();
        foreach ($pageLinksData as $name => $pageLinkData)
        {
            if ($searchPhrase)
            {
                $pageLinkData['search_phrase'] = $searchPhrase;
            }
            $urls[$name] = $routing->gen('items.list', $pageLinkData);
        }
        return $urls;
    }
}
    
?>
