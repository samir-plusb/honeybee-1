<?php

class Common_Service_DetectFaceAction extends CommonBaseAction
{
    public function executeRead(AgaviRequestDataHolder $parameters)
    {
        $asset = $parameters->getParameter(
            AssetInfoValidator::DEFAULT_EXPORT
        );

        $detector = new Face_Detector(
            AgaviConfig::get('core.lib_dir') .
            '/php-facedetection/detection.dat'
        );

        $aoi = array();
        if ($detector->face_detect($asset->getFullPath()))
        {
            $faceDims = $detector->getFace();

            $aoi = array(
                $faceDims['x'], 
                $faceDims['y'], 
                $faceDims['x'] + $faceDims['w'],
                $faceDims['y'] + $faceDims['w']
            );
        }
        
        $this->setAttribute('aoi', $aoi);

        return 'Success';
    }
}
