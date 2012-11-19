<?php

class Common_Header_HeaderSuccessView extends CommonBaseView
{
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);

        $user = $this->getContext()->getUser();
        $email = $user->getAttribute('email');
        $url = AgaviConfig::get('core.gravatar_url_tpl');
        $hash = md5('12345');
        if ($email)
        {
            $hash = md5(strtolower(trim($email)));
        }

        $url = str_replace('{EMAIL_HASH}', $hash, $url);
        $curl = ProjectCurl::create($url);
        $resp = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (200 === $status)
        {
            $this->setAttribute('avatar_url', $url);
        }
    }
}
