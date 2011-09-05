<?php

class Import_Imperia_ImperiaErrorView extends ImportBaseView
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
    public function executeJson(AgaviRequestDataHolder $rd)
    {
        $this->setupJson($rd);
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
    public function executeText(AgaviRequestDataHolder $rd)
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
