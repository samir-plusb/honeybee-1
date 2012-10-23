<?php

class Asset_UpdateAction extends AssetBaseAction
{
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $asset = $parameters->getParameter('asset');
        $metaData = $parameters->getParameter('metaData', array());
        ProjectAssetService::getInstance()->update($asset, $metaData);
        return 'Success';
    }  
}

?>