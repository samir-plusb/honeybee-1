<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Items
 */
class Items_List_ListSuccessView extends ItemsBaseView
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

        $this->setAttribute('_title', 'Items/List/Success@Html');
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
        $msg = "Items/List/Success@Text" . PHP_EOL;
        $msg .= var_export($this->getAttribute('data')->toArray(), TRUE);

        $this->getResponse()->setContent($msg);
    }
}
