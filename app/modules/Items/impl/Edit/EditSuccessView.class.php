<?php

/**
 * The Items_Edit_EditSuccessView class handles Items/Edit success data presentation.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Mvc
 */
class Items_Edit_EditSuccessView extends ItemsBaseView
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
        error_log(__FILE__ . __METHOD__ . error_log(__FILE__ . __METHOD__));

        $this->setupHtml($parameters, 'slot');
        $this->setAttribute('_title', 'Midas - News Refinement');
        $this->setAttribute('tag_options', array(
            'mandatory' => TRUE,
            'tags' => AgaviConfig::get('items.tags', array())
        ));

        $this->setAttribute(
            'category_options',
            AgaviConfig::get('items.categories', array())
        );

        WorkflowBaseInteractivePlugin::setPluginResultAttributes(
            $this->getContainer(),
            WorkflowInteractivePluginResult::STATE_EXPECT_INPUT,
            WorkflowPluginResult::GATE_DEFAULT,
            'Yay I can haz workflow message!'
        );

        error_log(__FILE__ . __METHOD__ . __LINE__);
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