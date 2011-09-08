<?php

/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Import
 */
class Import_Newswire_NewswireInputView extends ImportBaseView
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
        $data = array('state' => 'success');
        $this->getResponse()->setContent(json_encode($data));
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
        $this->getResponse()->setContent("Import succeeded.");
    }
}

?>