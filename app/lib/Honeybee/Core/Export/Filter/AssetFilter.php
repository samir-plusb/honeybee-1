<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Export\Config\IConfig;
use Honeybee\Agavi\Database\CouchDb\ClientException;
use Imagine;

class AssetFilter extends BaseFilter
{
    protected $client;

    public function __construct($name, IConfig $config)
    {
        parent::__construct($name, $config);

        $this->client = \AgaviContext::getInstance()->getDatabaseConnection(
            $this->getConfig()->get('connection')
        );
    }

    public function execute(Document $document)
    {
        $filterOutput = array();
        $assetProps = $this->getConfig()->get('properties');
        $documentShortId = $document->getShortIdentifier();

        foreach ($assetProps as $fieldname)
        {
            $filterOutput[$fieldname] = array();
            $fieldAssetIds = $document->getValue($fieldname);
            $fieldAssetIds = is_array($fieldAssetIds) ? $fieldAssetIds : array();

            foreach ($fieldAssetIds as $assetId)
            {
                $assetData = $this->buildAssetData(
                    \ProjectAssetService::getInstance()->get($assetId)
                );

                if (NULL === $assetData)
                {
                    // broken asset ...
                    continue;
                }

                $assetDoc = $this->loadAssetDoc($assetData['_id']);
                if ($assetDoc)
                {
                    // set revision so couchdb will update without complaining.
                    $assetData['_rev'] = $assetDoc['_rev'];
                }

                $assetData['sourceDoc'] = $documentShortId;

                // no 
                $this->client->storeDoc(NULL, $assetData);
                $filterOutput[$fieldname][] = $assetData['_id'];
            }
        }

        $this->cleanupOldAssets($document, $filterOutput);

        return $filterOutput;
    }

    protected function cleanupOldAssets(Document $document, array $output)
    {
        $previousAssetIds = $this->getReferencedAssetIds($document);
        $currentAssetIds = array();

        foreach ($output as $fieldname => $assetIds)
        {
            $currentAssetIds = array_merge($currentAssetIds, $assetIds);
        }

        foreach (array_diff($previousAssetIds, array_unique($currentAssetIds)) as $oldAssetId)
        {
            // in order to delete documents from couch we need the current revision.
            $assetRevision = $this->client->statDoc(NULL, $oldAssetId);

            if (0 !== $assetRevision)
            {
                $this->client->deleteDoc(NULL, $oldAssetId, $assetRevision);
            }
        }
    }

    protected function getReferencedAssetIds(Document $document)
    {
        $viewName = $this->getConfig()->get('document_asset_map_view');
        $viewParts = explode('.', $viewName);
        $designDoc = $viewParts[0];
        $viewKey = $viewParts[1];
        $viewParams = array('key' => $document->getShortIdentifier());
        $map = $this->client->getView(NULL, $designDoc, $viewKey, $viewParams);
        $assetIds = array();

        foreach ($map['rows'] as $row)
        {
            $assetIds[] = $row['value'];
        }

        return $assetIds;
    }

    protected function buildAssetData(\ProjectAssetInfo $asset)
    {
        $assetData = NULL;

        try
        {
            if (TRUE === $this->getConfig()->get('enable_aoi', FALSE))
            {
                $this->writeAoi($asset);
            }
            $metaData = $asset->getMetaData();
            $filePath = $asset->getFullPath();

            $imagine = new Imagine\Gd\Imagine();
            $image = $imagine->open($filePath);
            $size = $image->getSize();
            
            $assetData = array(
                '_id' => "asset-" . $asset->getIdentifier(),
                'data' => base64_encode(fread(fopen($filePath, 'r'), $asset->getSize())),
                'width' => $size->getWidth(),
                'height' => $size->getHeight(),
                'mime' => $asset->getMimeType(),
                'filename' => $asset->getFullName(),
                'modified' => date(DATE_ISO8601, filemtime($filePath)),
                'copyright' => isset($metaData['copyright']) ? $metaData['copyright'] : '',
                'copyrightUrl' => isset($metaData['copyright_url']) ? $metaData['copyright_url'] : '',
                'caption' => isset($metaData['caption']) ? $metaData['caption'] : '',
                'type' => 'asset'
            );
        }
        catch(\Exception $e)
        {
            error_log(__METHOD__ . ':' . $e->getMessage());
            $assetData = NULL;
        }
        
        return $assetData;
    }

    protected function writeAoi(\ProjectAssetInfo $asset)
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

            // @todo Use the LoggingService when it lands.
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
    }

    protected function loadAssetDoc($assetDocId)
    {
        $assetDocument = NULL;

        try
        {
            $assetDocument = $this->client->getDoc(NULL, $assetDocId);
        }
        catch(ClientException $e)
        {
            if (preg_match('~(\(404\))~', $e->getMessage()))
            {
                // no document for the given id in our current database.
                $assetDocument = NULL;
            }
            else
            {
                throw $e;
            }
        }

        return $assetDocument;
    }
}
