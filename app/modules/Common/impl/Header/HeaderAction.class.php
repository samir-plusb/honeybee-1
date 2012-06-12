<?php

class Common_HeaderAction extends CommonBaseAction
{
    const BREADCRUMB_NAMESPACE = 'midas.breadcrumbs';

    public function execute(AgaviRequestDataHolder $parameters)
    {
        $user = $this->getContext()->getUser();
        $breadcrumbs = array();
        $modulecrumb = NULL;
        if ($user->isAuthenticated())
        {
            $breadcrumbs = $user->getAttribute('breadcrumbs', self::BREADCRUMB_NAMESPACE, array());
            $modulecrumb = $user->getAttribute('modulecrumb', self::BREADCRUMB_NAMESPACE, NULL);
        }
        $this->setAttribute('breadcrumbs', $breadcrumbs);
        $this->setAttribute('modulecrumb', $modulecrumb);
        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}

?>