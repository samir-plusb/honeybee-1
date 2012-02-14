<?php

/**
 * The News_Edit_EditInputView class handles News/Edit read success data presentation.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class News_Edit_EditInputView extends NewsBaseView
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
        $this->setAttribute('_title', 'Midas - News Refinement');

        $this->setAttribute('tag_options', array(
            'mandatory' => FALSE,
            'tags' => AgaviConfig::get('news.tags', array())
        ));
        $this->setAttribute(
            'category_options',
            AgaviConfig::get('news.categories', array())
        );

        $ticket = $this->getAttribute('ticket');
        $item = $ticket->getWorkflowItem();
        $ticketData = $ticket->toArray();
        $ticketData['item'] = $item->toArray();
        $assetData = $this->prepareAssets($item->getImportItem());

        $this->setAttribute('ticket', $ticketData);
        $this->setAttribute('assets', $assetData);

        $routing = $this->getContext()->getRouting();
        $browseApiParams = array('cur_item' => '{CUR_ITEM}');
        $this->setAttribute('next_item_url', $routing->gen('news.api.next_item', $browseApiParams));
        $this->setAttribute('prev_item_url', $routing->gen('news.api.prev_item', $browseApiParams));
        $this->setAttribute('nearby_url', $routing->gen(
            'news.api.items_nearby',
            array('lon' => '{LONGITUDE}', 'lat' => '{LATITUDE}'))
        );
        $listFilter = array(
            'limit' => $parameters->getParameter('limit'),
            'offset' => $parameters->getParameter('offset'),
            'sorting' => $parameters->getParameter('sorting', array(
                'direction' => 'desc',
                'field' => 'timestamp'
            ))
        );
        if ($parameters->hasParameter('search_phrase'))
        {
            $listFilter['search_phrase'] = $parameters->getParameter('search_phrase');
        }
        $this->setAttribute('list_filter', $listFilter);
        $this->setAttribute('list_url', $routing->gen('news.list', $listFilter));
        $this->setAttribute('release_url', $routing->gen('workflow.release', array('ticket' => '{TICKET_ID}')));
        $this->setAttribute('grab_url', $routing->gen('workflow.grab', array(
            'ticket' => array(
                'id' => '{TICKET_ID}',
                'rev' => '{TICKET_REV}'
             )
        )));
        $this->setAttribute('editor', $this->getContext()->getUser()->getAttribute('login'));
        $this->setAttribute('edit_view_routes', array(
            'api_extract_date' => $routing->gen('news.api.extract_date'),
            'api_extract_location' => $routing->gen('news.api.extract_location'),
            'api_delete_item' => $routing->gen('news.api.delete_item'),
            'workflow_run' => $routing->gen('workflow.run'),
            'workflow_proceed' => $routing->gen('workflow.proceed')
        ));
    }

    protected function prepareAssets(IImportItem $item)
    {
        $assetService = ProjectAssetService::getInstance();
        $assets = array();
        $routing = $this->getContext()->getRouting();
        foreach ($item->getMedia() as $mediaId)
        {
            $asset = $assetService->get($mediaId);
            $curAssetData = $asset->toArray();
            $curAssetData['url'] = $routing->gen('asset.binary', array('aid' => $mediaId));
            $assets[] = $curAssetData;
        }

        return $assets;
    }

    /**
     * Handle presentation logic for commandline interfaces.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $msg = "News/Edit/Success@Text" . PHP_EOL;
        $this->getResponse()->setContent($msg);
    }

    /**
     * Handle presentation logic for commandline interfaces.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->getResponse()->setContent(json_encode($this->getAttribute('items')));
    }
}

?>
