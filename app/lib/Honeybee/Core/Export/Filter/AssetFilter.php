<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\Document;
use Imagine;

class AssetFilter extends BaseFilter
{
    public function execute(Document $document)
    {
        $output = array();

        $assetProps = $this->getConfig()->get('properties');

        foreach ($assetProps as $fieldname)
        {
            $assetIds = $document->getValue($fieldname);
            $assetIds = is_array($assetIds) ? $assetIds : array();

            foreach ($assetIds as $assetId)
            {
                $output[] = $this->buildAssetData(
                    \ProjectAssetService::getInstance()->get($assetId)
                );
            }
        }

        return $output;
    }

    protected function buildAssetData(\ProjectAssetInfo $asset)
    {
        $assetData = NULL;

        try
        {
            $metaData = $asset->getMetaData();
            $filePath = $asset->getFullPath();
            $exifStatus = 1;
            $output = array();

            if (isset($metaData['aoi']) && is_array($metaData['aoi']) && 4 === count($metaData['aoi']))
            {   
                $exifConfig = \AgaviConfig::get('core.config_dir') . DIRECTORY_SEPARATOR . 'exiftool.conf';

                $exifToolCommand = sprintf('exiftool -config %s -AOI="%s" %s',
                    $exifConfig, implode(',', $metaData['aoi']), $filePath
                );

                exec($exifToolCommand, $output, $exifStatus);

                file_put_contents(
                    \AgaviConfig::get('core.app_dir') . '/log/exif_tool.log', 
                    date('Y-m-d H:i:s') . ': ' . $exifToolCommand . ' -> ' . implode(PHP_EOL, $output) . PHP_EOL, 
                    FILE_APPEND
                );
            }   
            else
            {   
                $exifConfig = \AgaviConfig::get('core.config_dir') . DIRECTORY_SEPARATOR . 'exiftool.conf';
                $exifToolCommand = sprintf('exiftool -config %s -AOI="" %s', $exifConfig, $filePath);  

                exec($exifToolCommand, $output, $exifStatus);
            }   

            if ('1' == $exifStatus)
            {   
                throw new \Exception("Unable to write aoi information to binary: " . implode(PHP_EOL, $output));
            }

            $imagine = new Imagine\Gd\Imagine();
            $image = $imagine->open($filePath);
            $size = $image->getSize();
            
            $assetData = array(
                'identifier' => "asset-" . $asset->getIdentifier(),
                'data' => base64_encode(fread(fopen($filePath, 'r'), $asset->getSize())),
                'width' => $size->getWidth(),
                'height' => $size->getHeight(),
                'mime' => $asset->getMimeType(),
                'filename' => $asset->getFullName(),
                'modified' => date(DATE_ISO8601, filemtime($filePath)),
                'copyright' => isset($metaData['copyright']) ? $metaData['copyright'] : '',
                'copyrightUrl' => isset($metaData['copyright_url']) ? $metaData['copyright_url'] : '',
                'caption' => isset($metaData['caption']) ? $metaData['caption'] : ''
            );
        }
        catch(\Exception $e)
        {
            error_log(__METHOD__ . ':' . $e->getMessage());
            $assetData = NULL;
        }
        
        return $assetData;
    }
}
