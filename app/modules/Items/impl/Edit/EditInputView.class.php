<?php

/**
 * The Items_Edit_EditInputView class handles Items/Edit read success data presentation.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Mvc
 */
class Items_Edit_EditInputView extends ItemsBaseView
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
            'tags' => AgaviConfig::get('items.tags', array())
        ));
        $this->setAttribute(
            'category_options',
            AgaviConfig::get('items.categories', array())
        );

        $ticket = $this->getAttribute('ticket');
        $item = $ticket->getWorkflowItem();
        $ticketData = $ticket->toArray();
        $ticketData['item'] = $item->toArray();
        $ro = $this->getContext()->getRouting();
        $this->setAttribute('ticket', $ticketData);
        $browseApiParams = array('cur_item' => '{CUR_ITEM}');
        $this->setAttribute('next_item_url', $ro->gen('items.api.next_item', $browseApiParams));
        $this->setAttribute('prev_item_url', $ro->gen('items.api.prev_item', $browseApiParams));
        $this->setAttribute('nearby_url', $ro->gen(
            'items.api.items_nearby',
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
        $this->setAttribute('list_url', $ro->gen('items.list', $listFilter));
        $this->setAttribute('release_url', $ro->gen('workflow.release', array('ticket' => '{TICKET_ID}')));
        $this->setAttribute('grab_url', $ro->gen('workflow.grab', array(
            'ticket' => array(
                'id' => '{TICKET_ID}',
                'rev' => '{TICKET_REV}'
             )
        )));
        $this->setAttribute('editor', $this->getContext()->getUser()->getAttribute('login'));
        $this->setAttribute('edit_view_routes', array(
            'api_extract_date' => $ro->gen('items.api.extract_date'),
            'api_extract_location' => $ro->gen('items.api.extract_location'),
            'api_delete_item' => $ro->gen('items.api.delete_item'),
            'workflow_run' => $ro->gen('workflow.run'),
            'workflow_proceed' => $ro->gen('workflow.proceed')
        ));
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
        $msg = "Items/Edit/Success@Text" . PHP_EOL;
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
