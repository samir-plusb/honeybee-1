<?php

class FrontendMovieAssetFactory
{
    public function createAssets(IDataObject $dataObject, array $options = array())
    {
        $assetDocuments = array();
        foreach ($this->mapAssetsData($dataObject->getMasterRecord()->getMedia()) as $assetData)
        {
            $assetDocuments[] = FrontendMovieAssetDocument::fromArray($assetData);
        }
        return $assetDocuments;
    }

    protected function mapAssetsData(array $media)
    {
        $assets = array();
        $assetKeys = array('scene', 'poster', 'poster600', 'hdPic');
        $galleryKeys = array('standard', 'big');

        foreach ($assetKeys as $key)
        {
            if (isset($media['images'][$key]))
            {
                $assets[] = $this->buildAssetData($media['images'][$key]);
            }
        }
        foreach ($galleryKeys as $key)
        {
            if (isset($media['galleries'][$key]))
            {
                $assets = array_merge($this->buildAssetsData($media['galleries'][$key]), $assets);
            }
        }
        return $assets;
    }

    protected function buildAssetsData(array $assetIds)
    {
        $assetsData = array();
        foreach ($assetIds as $assetId)
        {
            if (($data = $this->buildAssetData($assetId)))
            {
                $assetsData[] = $data;
            }
        }
        return $assetsData;
    }

    protected function buildAssetData($assetId)
    {
        $assetData = NULL;
        if (NULL !== $assetId && ($asset = ProjectAssetService::getInstance()->get($assetId)))
        {
            $metaData = $asset->getMetaData();
            $imagine = new Imagine\Gd\Imagine();
            $filePath = $asset->getFullPath();
            $image = $imagine->open($filePath);
            $size = $image->getSize();
            $assetData = array(
                'identifier' => "asset-$assetId",
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
        return $assetData;
    }
}
