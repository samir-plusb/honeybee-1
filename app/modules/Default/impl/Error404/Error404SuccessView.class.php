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
     * force error response as text/html
     *
     * @author tay
     * @since 08.10.2011
     * @param AgaviRequestDataHolder $rd
     * @return AgaviExecutionContainer
     */
    public function executePng(AgaviRequestDataHolder $rd)
    {
        return $this->executeAny($rd);
    }

    /**
     * force error response as text/html
     *
     * @author tay
     * @since 08.10.2011
     * @param AgaviRequestDataHolder $rd
     * @return AgaviExecutionContainer
     */
    public function executeSvg(AgaviRequestDataHolder $rd)
    {
        return $this->executeAny($rd);
    }

    /**
     * force error response as text/html
     *
     * @author tay
     * @since 08.10.2011
     * @param AgaviRequestDataHolder $rd
     * @return AgaviExecutionContainer
     */
    public function executeXml(AgaviRequestDataHolder $rd)
    {
        $response = $this->getContainer()->getResponse();
        /* @var $response AgaviWebResponse */
        $response->setHttpStatusCode($this->getAttribute('status', 404));

        return '<?xml version="1.0" encoding="UTF-8"?><error>'.$this->encodeXml($this->toArray()).'</error>';
    }

    /**
     * Encode the given array to a simple xml
     *
     * array keys are converted to tag names and content is convertert to pcdata. Array values must be strings
     * or array.
     *
     * @param array $data array to encode
     * @return string xml
     */
    protected function encodeXml(array $data)
    {
        $inner = '';
        foreach ($data as $key => $val)
        {
            if (is_array($val))
            {
                $inner .= sprintf("<%s>\n2$s</%1$s>\n", $key, $this->encodeXml($val));
            }
            else
            {
                $inner .= sprintf("<%1$s>%<![CDATA[2$s]]></%1$s>\n", $key, htmlspecialchars($val));
            }
        }
        return $inner;
    }

    /**
     * force error response as text/html
     *
     * @author tay
     * @since 08.10.2011
     * @param AgaviRequestDataHolder $rd
     * @return AgaviExecutionContainer
     */
    public function executeRss(AgaviRequestDataHolder $rd)
    {
        return $this->executeAny($rd);
    }

    /**
     * force error response as text/html
     *
     * @author tay
     * @since 08.10.2011
     * @param AgaviRequestDataHolder $rd
     * @return AgaviExecutionContainer
     */
    public function executeKml(AgaviRequestDataHolder $rd)
    {
        return $this->executeAny($rd);
    }

    /**
     * force error response as text/html
     *
     * @author tay
     * @since 08.10.2011
     * @param AgaviRequestDataHolder $rd
     * @return AgaviExecutionContainer
     */
    public function executeAny(AgaviRequestDataHolder $rd)
    {
        return $this->createForwardContainer(
        $this->getContainer()->getModuleName(),
        $this->getContainer()->getActionName(),
        NULL, 'html', 'read');
    }

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
        $this->setAttribute('_title', $this->translationManager->_($this->getAttribute('_title', '404 Not Found')));

        $response = $this->getContainer()->getResponse();
        /* @var $response AgaviWebResponse */
        $response->setContentType('text/html');

        $this->container->getResponse()->setHttpStatusCode($this->getAttribute('status', 404));
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
        $response = $this->getContainer()->getResponse();
        /* @var $response AgaviWebResponse */
        $response->setHttpStatusCode($this->getAttribute('status', 404));
        $response->setContentType('application/json');

        return json_encode($this->toArray());
    }


    /**
     * get attributes as array for json and xml output
     */
    protected function toArray()
    {
        $result = array(
            'result' => 'error',
            'message' => '404 - Not found',
            'module' => $this->getAttribute('_module', NULL),
            'action' => $this->getAttribute('_action', NULL),
            'method' => $this->getAttribute('method', NULL)
        );
        $errors = array();
        foreach ($this->getAttribute('errors', array()) as $error)
        {
            if ($error instanceof AgaviValidationError)
            {
                $errors[] = array('arguments' => implode(',',$error->getFields()), 'message' => $error->getMessage());
            }
        }
        $result['errors'] = $errors;

        return $result;
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
        $tpl404 = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'action.list';
        $content404 = file_get_contents($tpl404);

        $this->getResponse()->setContent($content404);
    }

}

?>