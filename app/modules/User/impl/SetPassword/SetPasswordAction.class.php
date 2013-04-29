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

        return 'Error';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
