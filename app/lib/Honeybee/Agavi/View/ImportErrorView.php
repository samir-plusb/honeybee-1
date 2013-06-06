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

        if (!$this->getResponse()->getParameter('append_eol', true))
        {
            $error_message .= PHP_EOL;
        }

        $this->getResponse()->setExitCode(1);

        /*
         * we just send stuff to STDERR as AgaviResponse::sendContent() uses fpassthru which
         * does not allow us to give the handle to Agavi via $rp->setContent() or return $handle
         * notice though, that the shell exit code will still be set correctly
         */
        if (php_sapi_name() === 'cli' && defined('STDERR'))
        {
            fwrite(STDERR, $error_message);
            fclose(STDERR);
        }
        else
        {
            return $error_message;
        }
    }
}
