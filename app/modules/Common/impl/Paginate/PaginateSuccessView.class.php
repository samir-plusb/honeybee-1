<?php

/**
 * The Common_Paginate_PaginateSuccessView class handles Common/Paginate success data presentation.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class Common_Paginate_PaginateSuccessView extends CommonBaseView
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
        $listConfig = $parameters->getParameter('config');
        $this->setAttribute('links', $this->generatePagingLinks($listConfig->getRouteName()));
    }

    protected function generatePagingLinks($listRoute)
    {
        $routing = $this->getContext()->getRouting();
        $limit = $this->getAttribute('limit');
        $currentOffset = $this->getAttribute('offset');
        $currentPage = (int)floor($currentOffset / $limit);
        $lastPage = $this->getAttribute('last_page');
        $search = $this->getAttribute('search', FALSE);
        $filter = $this->getAttribute('filter', FALSE);
        $sorting = $this->getAttribute('sorting', FALSE);
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
            if ($search)
            {
                $pageLinkData['search'] = $search;
            } 
            else if ($filter)
            {
                $pageLinkData['filter'] = $filter;
            }
            if ($sorting)
            {
                $pageLinkData['sorting'] = $sorting;
            }
            $urls[$name] = $routing->gen($listRoute, $pageLinkData);
        }
        return $urls;
    }
}

?>
