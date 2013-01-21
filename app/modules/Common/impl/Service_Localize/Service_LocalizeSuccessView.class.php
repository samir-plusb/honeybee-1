<?php

class Common_Service_Localize_Service_LocalizeSuccessView extends CommonBaseView
{
    public function executeJson(AgaviRequestDataHolder $parameters) 
    {
        $this->getResponse()->setContent(
            htmlspecialchars_decode(json_encode(
                array(
                    'state' => 'ok',
                    'location' => $this->getAttribute('location')
                )
            )
        ));
    }
}
