<?php

class UserTokenValidator extends AgaviValidator
{
    protected function validate()
    {
        $user = NULL;

        if (! ($user = $this->loadUser()))
        {
            return FALSE;
        }

        $this->export($this->getParameter('export', 'user'), $user);

        return TRUE;
    }

    protected function loadUser()
    {
        $tokenArgumentName = $this->getParameter('token_argument_name');

        if (! $tokenArgumentName)
        {
            return FAlSE;
        }

        $token = $this->getData($tokenArgumentName);
        if (! $token)
        {
            return FALSE;
        }

        $module = $module = $this->getContext()->getRequest()->getAttribute('module', 'org.honeybee.env');
        $service = $module->getService();
        $userSearchSpec = array('filter' => array('authToken.filter' => $token));
        $result = $service->find($userSearchSpec, 0, 1);

        if (1 === $result['totalCount'])
        {
            $user = $result['documents']->first();

            $expireDate = new DateTime($user->getTokenExpireDate());
            $now = new DateTime();
            $dateDiff = $now->diff($expireDate);

            if (1 === $dateDiff->invert)
            {
                return FALSE;
            }
        }
        else
        {
            return FALSE;
        }

        return $user;
    }
}
