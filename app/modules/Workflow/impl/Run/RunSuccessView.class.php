<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Workflow
 */
class Workflow_Run_RunSuccessView extends ProjectBaseView
{
    /**
     * Handles the Html output type.
     *
     * @parameter  AgaviRequestDataHolder the (validated) request data
     *
     * @return     mixed <ul>
     *                     <li>An AgaviExecutionContainer to forward the execution to or</li>
     *                     <li>Any other type will be set as the response content.</li>
     *                   </ul>
     */
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        return $this->getAttribute('content');
    }

    /**
     * Prepares and sets our text data on our console response.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        return $this->getAttribute('content');
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
        return json_encode(
            array(
                'state' => 'ok',
                'msg' => $this->getAttribute('content')
            )
        );
    }
}

?>
