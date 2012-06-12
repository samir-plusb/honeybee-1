<?php

/**
 * The News_Import_ProcMail_Import_ProcMailSuccessView class handle the presentation logic
 * for our News/Import_ProcMail actions's success data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class News_Import_ProcMail_Import_ProcMailSuccessView extends NewsBaseView
{
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
        $this->getResponse()->setContent(
            "Successfully imported your procmail mime-mail." . PHP_EOL
        );
    }
}

?>