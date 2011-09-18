<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * The Default_Error404_Error404SuccessView class provides presentation logic for standard 404 handling.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Default
 * @subpackage      Mvc
 */
class Default_Error404_Error404SuccessView extends DefaultBaseView
{
    /**
     * Execute any html related presentation logic and sets up our template attributes.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeHtml(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->setupHtml($parameters);
        $this->setAttribute('_title', $this->translationManager->_('404 Not Found'));
        $this->container->getResponse()->setHttpStatusCode('404');
    }

    /**
     * Prepares and sets our json data on our webresponse.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->getContainer()->getResponse()->setContent(
            json_encode(
                array(
                    'result' => 'error',
                    'message' => '404 - Not found'
                )
            )
        );
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
        $lines = array();
        $lines[] = PHP_EOL . "####################################" . PHP_EOL;
        $lines[] = "Usage: bin/cli <command> [OPTION]" . PHP_EOL;
        $lines[] = "Available Commands:";
        $lines[] = "-------------------------------------";
        $lines[] = PHP_EOL . "- Import Module";
        $lines[] = "    import.run -i (import) [-d (src1), (src2) ...]";
        $lines[] = "    import.imperia -data (json)";
        $lines[] = PHP_EOL . "- Asset Module";
        $lines[] = "    asset.setup";
        $lines[] = "    asset.put -asset (uri)";
        $lines[] = "    asset.get -aid (asset-id)";
        $lines[] = "    asset.delete -aid (asset-id)";
        $lines[] = PHP_EOL . "- Items Module";
        $lines[] = "    items.setup";
        $lines[] = "    items.list";
        
        $lines[] = PHP_EOL . "####################################";
        $this->getResponse()->setContent(implode(PHP_EOL, $lines));
    }

}

?>