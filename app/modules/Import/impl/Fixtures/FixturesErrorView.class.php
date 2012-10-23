<?php

/**
 * The Import_Fixtures_FixturesErrorView class handles the fixtures import error presentation.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Mvc
 */
class Import_Fixtures_FixturesErrorView extends ImportBaseView
{
    /**
     * Handles the Console output type.
     *
     * @parameter  AgaviRequestDataHolder the (validated) request data
     *
     * @return     mixed <ul>
     *                     <li>An AgaviExecutionContainer to forward the execution to or</li>
     *                     <li>Any other type will be set as the response content.</li>
     *                   </ul>
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $content = implode("\n", $this->getErrorMessages());
        $content = 'An error occoured: ' . $content;
        $this->getResponse()->setContent($content);
    }
}

?>