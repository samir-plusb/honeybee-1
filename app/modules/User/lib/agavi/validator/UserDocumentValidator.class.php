<?php

use Honeybee\Agavi\Validator\DocumentValidator;
use Honeybee\Core\Security\Auth\TokenGenerator;

class UserDocumentValidator extends DocumentValidator
{
    protected function validate()
    {
        $success = TRUE; 

        if ('console' === $this->getContext()->getName())
        {
            // validate username and password
            // meaning: $success = FALSE; if they do not comply

            if ($success)
            {
                $expireDate = new DateTime();
                $expireDate->add(new DateInterval('PT20M'));

                $document = $this->getModule()->createDocument(array(
                    'username' => $this->getData('username'),
                    'email' => $this->getData('email'),
                    'role' => 'honeybee-editor',
                    'authToken' => TokenGenerator::generateToken(),
                    'tokenExpireDate' => $expireDate->format(DATE_ISO8601)
                ));

                $this->export($document, $this->getParameter('export', 'document'));
            }
        }
        else
        {
            $success = parent::validate();
        }

        return $success;
    }
}
