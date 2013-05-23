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
            $this->throwError('missing_token_argument_parameter');
            return FAlSE;
        }

        $token = $this->getData($tokenArgumentName);
        if (! $token)
        {
            $this->throwError('missing_token');
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
                $this->throwError('invalid_token');
                return FALSE;
            }
        }
        else
        {
            $this->throwError('invalid_token');
            return FALSE;
        }

        return $user;
    }
}
