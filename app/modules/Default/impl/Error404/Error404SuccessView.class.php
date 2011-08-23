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

class Default_Error404_Error404SuccessView extends DefaultBaseView 
{
    public function executeHtml(AgaviRequestDataHolder $rd) 
    {
        $this->setupHtml($rd);
        $this->setAttribute('_title', $this->tm->_('404 Not Found'));
        $this->container->getResponse()->setHttpStatusCode('404');
    }

    public function executeText(AgaviRequestDataHolder $rd) 
    {
        return 'Usage: console.php <command> [OPTION]...' . PHP_EOL .
            PHP_EOL . 'Available Commands:' . PHP_EOL;
    }
}

?>