<?php

use Honeybee\Domain\User\UserDocument;

/**
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         User
 */
class User_ResetPasswordAction extends UserBaseAction
{
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $translationManager = $this->getContext()->getTranslationManager();

        try 
        {
            $result = $this->getModule()->getService()->find(
                array('filter' => $this->buildUserFilter($parameters)), 0, 10
            );

            if (1 === $result['totalCount'])
            {
                $this->getModule()->getService()->sendPasswordLostEmail(
                    $result['documents']->first()
                );
            }
            else
            {
                $this->setAttribute(
                    'errors', 
                    array('invalid_account' => $translationManager->_('invalid_account', 'user.messages'))
                );

                return 'Input';
            }

            return 'Success';            
        } 
        catch (Exception $e) 
        {
            $this->setAttribute('errors', array('unexpected' => $e->getMessage()));
            return 'Input';            
        }        
    }

    public function executeRead(AgaviRequestDataHolder $parameters)
    {
        return 'Input';
    }

    public function handleError(AgaviRequestDataHolder $parameters)
    {
        parent::handleError($parameters);

        $translationManager = $this->getContext()->getTranslationManager();
        $this->setAttribute('errors', array(
            $translationManager->_('invalid_account', 'user.messages')
        ));

        return 'Input';
    }

    public function isSecure()
    {
        return FALSE;
    }

    protected function buildUserFilter(AgaviRequestDataHolder $parameters)
    {
        $userFilter = array();

        if ($parameters->hasParameter('email'))
        {
            $userFilter['email.filter'] = $parameters->getParameter('email');
        }
        else
        {
            $userFilter['username.filter'] = $parameters->getParameter('username');
        }

        return $userFilter;
    }
}