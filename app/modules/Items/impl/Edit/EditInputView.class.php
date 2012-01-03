<?php

/**
 * The Items_Edit_EditInputView class handles Items/Edit success data presentation.
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
     *E
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeHtml(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->setupHtml($parameters);
        $this->setAttribute('_title', 'Midas - News Refinement');

        $this->setAttribute('tag_options', array(
            'mandatory' => TRUE,
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

        $this->setAttribute('ticket', $ticketData);
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
