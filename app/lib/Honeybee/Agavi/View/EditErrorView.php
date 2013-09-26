<?php

namespace Honeybee\Agavi\View;

class EditErrorView extends BaseView
{
    public function executeJson(\AgaviRequestDataHolder $parameters)
    {
        $data = array(
            'state' => 'error',
            'messages' => array(),
            'errors' => $this->getAttribute('errors', array()),
            'data' => array()
        );

        $this->getResponse()->setContent(json_encode($data));
    }

    public function executeConsole(\AgaviRequestDataHolder $request_data)
    {
        $error_message = "Processing error in " . __METHOD__;

        if ($this->hasAttribute('message'))
        {
            $error_message .= ' Message: ' . $this->getAttribute('message', '');
        }

        if ($this->hasAttribute('errors'))
        {
            $error_message .= PHP_EOL . PHP_EOL . "Details: " . PHP_EOL;
            foreach ($this->getAttribute('errors') as $error)
            {
                $error_message .= "-$error" . PHP_EOL;
            }
        }

        return $this->cliError($error_message);
    }
}
