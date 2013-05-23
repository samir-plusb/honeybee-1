<?php

/**
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         User
 * @subpackage      SetPassword
 */
class User_SetPasswordAction extends UserBaseAction
{
    public function executeRead(AgaviRequestDataHolder $parameters)
    {
        return 'Input';
    }

    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $this->getModule()->getService()->save(
            $parameters->getParameter('user')
        );

        return 'Success';
    }

    public function handleError(AgaviRequestDataHolder $parameters)
    {
        parent::handleError($parameters);

        $errors = array();
        $view = 'Input';

        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $errMsg)
        {
            if ($errMsg['errors'][0] === 'token')
            {
                $view = 'Error';
            }
            $errors[] = $errMsg['message'];
        }

        $this->setAttribute('errors', $errors);
        
        return $view;
    }

    public function isSecure()
    {
        return FALSE;
    }
}
