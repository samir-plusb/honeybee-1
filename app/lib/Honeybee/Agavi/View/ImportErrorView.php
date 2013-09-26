<?php

namespace Honeybee\Agavi\View;

class ImportErrorView extends BaseView
{
    public function executeConsole(\AgaviRequestDataHolder $request_data)
    {
        $error_message = "Error while trying to import.";

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
