<?php

/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Import
 */
class Import_Newswire_NewswireErrorView extends ImportBaseView
{
    /**
     * Handles the Json output type.
     *
     * @parameter  AgaviRequestDataHolder the (validated) request data
     *
     * @return     mixed <ul>
     *                     <li>An AgaviExecutionContainer to forward the execution to or</li>
     *                     <li>Any other type will be set as the response content.</li>
     *                   </ul>
     */
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        $this->setupJson($parameters);
        $errors = array();

        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $error)
        {
            $errors[] = $error['message'];
        }

        $this->getResponse()->setContent(
            json_decode(
                array('state' => 'error', 'errors' => $errors)
            )
        );
    }

    /**
     * Handles the Console output type.
     *
     * @parameter  AgaviRequestDataHolder the (validated) request data
     *
     * @return     mixed <ul>
     *                     <li>An AgaviExecutionContainer to forward the execution to or</li>
     *                     <li>Any other type will be set as the response content.</li>
     *                   </ul>
     */
    public function executeText(AgaviRequestDataHolder $parameters)
    {
        $errors = array();

        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $error)
        {
            $errors[] = $error['message'];
        }

        $content = implode("\n", $errors);

        $this->getResponse()->setContent($content);
    }
}

?>