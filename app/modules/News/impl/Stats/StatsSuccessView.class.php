<?php

/**
 * The News_Stats_StatsSuccessView class handles News/Stats success data presentation.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class News_Stats_StatsSuccessView extends NewsBaseView
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
        $this->setAttribute('_title', 'News Stats');
        $this->setBreadcrumb();
    }

    /**
     * Handle presentation logic for the json requests.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->getResponse()->setContent(json_encode(
            array('state' => 'ok', 'data' => $this->getAttribute('statistics'))
        ));
    }

    protected function setBreadcrumb()
    {
        $routing = $this->getContext()->getRouting();
        $breadcrumbs = $this->getContext()->getUser()->getAttribute('breadcrumbs', 'midas.breadcrumbs', array());
        if (1 <= count($breadcrumbs))
        {
            array_splice($breadcrumbs, 1);
        }
        $breadcrumbs = array(array(
            'text' => 'Statistik',
            'link' => $routing->gen('news.stats'),
            'info' => 'Lokalnachrichten - Statistik',
            'icon' => 'icon-graph'
        ));
        
        $this->getContext()->getUser()->setAttribute('breadcrumbs', $breadcrumbs, 'midas.breadcrumbs');
    }
}
