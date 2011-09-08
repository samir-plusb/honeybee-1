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
 * The Default_Index_IndexSuccessView class provides presentation logic for the %system_actions.default% action.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         ApplicationBase
 * @subpackage      Default
 */
class Default_Index_IndexSuccessView extends DefaultBaseView 
{
    /**
     * Execute any html related presentation logic and sets up our template attributes.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     */
    public function executeHtml(AgaviRequestDataHolder $parameters) 
    {
        $this->setupHtml($parameters);

        // set the title
        $this->setAttribute('_title', $this->translationManager->_('Welcome to the ContentWorker web frontend.'));
    }
    
    /**
     * Prepares and sets our json data on our webresponse.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     */
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        $this->getContainer()->getResponse()->setContent(
            json_encode(
                array(
                    'result' => 'error',
                    'message' => 'Welcome to the ContentWorker JSON API.'
                )
            )
        );
    }

    /**
     * Prepares and sets our json data on our console response.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     */
    public function executeText(AgaviRequestDataHolder $parameters)
    {
        $msg = 'Welcome to the ContentWorker CLI Interface.' . PHP_EOL;

        $this->getResponse()->setContent($msg);
    }
}

?>