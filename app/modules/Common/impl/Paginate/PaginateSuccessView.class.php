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

        $listState = $parameters->getParameter('state');
        $referenceModule = $listState->getReferenceModule();
        $referenceField = $listState->getReferenceField();
        $referenceFieldId = $listState->getReferenceFieldId();

        $this->setAttribute('reference_field', $referenceField);
        $this->setAttribute('reference_field_id', $referenceFieldId);
        $this->setAttribute('reference_module', $referenceModule);
        $this->setAttribute('links', $this->generatePagingLinks($parameters));
        $this->setAttribute('list_filter', $this->getAttribute('filter'));
    }

    protected function generatePagingLinks(AgaviRequestDataHolder $parameters)
    {
        $listConfig = $parameters->getParameter('config');
        $listState = $parameters->getParameter('state');
        $listRoute = $listConfig->getRouteName();
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
                'offset' => 0,
		'only_assigned_docs' => $this->getAttribute('only_assigned_docs')
            ),
            'last_page' => array(
                'limit' => $limit,
                'offset' => $lastPage * $limit,
		'only_assigned_docs' => $this->getAttribute('only_assigned_docs')
            ),
            'second_page' => array(
                'limit' => $limit,
                'offset' => $limit,
		'only_assigned_docs' => $this->getAttribute('only_assigned_docs')
            ),
            'second_last_page' => array(
                'limit' => $limit,
                'offset' => ($lastPage - 1) * $limit,
		'only_assigned_docs' => $this->getAttribute('only_assigned_docs')
            ),
            'previous_page' => array(
                'limit' => $limit,
                'offset' => ($currentPage - 1) * $limit,
		'only_assigned_docs' => $this->getAttribute('only_assigned_docs')
            ),
            'next_page' => array(
                'limit' => $limit,
                'offset' => ($currentPage + 1) * $limit,
		'only_assigned_docs' => $this->getAttribute('only_assigned_docs')
            )
        );

        $referenceModule = $listState->getReferenceModule();
        $referenceField = $listState->getReferenceField();
        $referenceFieldId = $listState->getReferenceFieldId();

        $defaultParams = array();
        if ($referenceModule && $referenceField)
        {
            $defaultParams['referenceModule'] = $referenceModule;
            $defaultParams['referenceField'] = $referenceField;
            $defaultParams['referenceFieldId'] = $referenceFieldId;
        }

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
            $urls[$name] = $routing->gen($listRoute, array_merge($defaultParams, $pageLinkData));
        }
        return $urls;
    }
}
