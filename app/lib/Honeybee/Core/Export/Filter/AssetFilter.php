<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\BaseDocument;
use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Config\IConfig;
use Honeybee\Core\Storage\CouchDb;
use Imagine;

class AssetFilter extends BaseFilter
{
    protected $storage;

    public function __construct($name, IConfig $config)
    {
        parent::__construct($name, $config);

        $database = \AgaviContext::getInstance()->getDatabaseManager()->getDatabase(
            $this->getConfig()->get('connection')
        );
        $this->storage = new CouchDb\GenericStorage($database);
    }

    public function execute(BaseDocument $document)
    {
        $filterOutput = array();
        $assetProps = $this->getConfig()->get('properties');
        $documentShortId = null;

        if ($document instanceof Document) {
            $documentShortId = $document->getShortIdentifier();
        }

        foreach ($assetProps as $fieldname => $exportFieldname)
        {
            $filterOutput[$exportFieldname] = array();
            $fieldAssetIds = $document->getValue($fieldname);
            $fieldAssetIds = is_array($fieldAssetIds) ? $fieldAssetIds : array();

            foreach ($fieldAssetIds as $assetId)
            {
                $assetData = $this->buildAssetData(\ProjectAssetService::getInstance()->get($assetId));

                if (! $assetData)
                {
                    // broken asset ...
                    continue;
                }

                if ($documentShortId) {
                    $assetData['sourceDoc'] = $documentShortId;
                }

                $this->storage->write($assetData);

                $assetExportFields = $this->getConfig()->get('asset_fields', array('identifier' => 'id'));

                $exportValue = array();
                foreach ($assetExportFields as $metaDataKey => $exportKey)
                {
                    $exportValue[$exportKey] = $assetData[$metaDataKey];
                }

                $filterOutput[$exportFieldname][] = $exportValue;
            }
        }

        if ($document instanceof Document) {
            $this->cleanupOldAssets($document, $filterOutput);
        }
        return $filterOutput;
    }

    public function onDocumentRevoked(BaseDocument $document)
    {
        if ($document instanceof Document) {
            foreach ($this->getReferencedAssetIds($document) as $assetId) {
                if (($asset = $this->storage->read($assetId))) {
                    $this->storage->delete($asset['identifier'], $asset['revision']);
                }
            }
        }
    }

    protected function cleanupOldAssets(BaseDocument $document, array $filterOutput)
    {
        $previousAssetIds = $this->getReferencedAssetIds($document);
        $currentAssetIds = array();

        foreach ($filterOutput as $fieldname => $assets)
        {
            foreach ($assets as $assetData)
            {
                $currentAssetIds[] = $assetData['id'];
            }
        }

        foreach (array_diff($previousAssetIds, array_unique($currentAssetIds)) as $oldAssetId)
        {
            if (($oldAsset = $this->storage->read($oldAssetId)))
            {
                $this->storage->delete($oldAsset['identifier'], $oldAsset['revision']);
            }
        }
    }

    // this part is very couchdb specific, while using the 'couchdb views' feature to look up related assets.
    // this will be tricky to abstract if we dont want to call this a CouchDbAssetFilter,
    // which we would have to do right now if were strict about concise naming ^^.
    protected function getReferencedAssetIds(BaseDocument $document)
    {
        if (! $this->getConfig()->has('document_asset_map_view'))
        {
            throw new \Exception(
                "Missing setting 'document_asset_map_view' for the AssetFilter's couchdb view name to use for mapping assets to documents."
            );
        }

        $viewName = $this->getConfig()->get('document_asset_map_view');

        $viewParts = explode('.', $viewName);
        $designDoc = $viewParts[0];
        $viewKey = $viewParts[1];
        $viewParams = array('key' => $document->getShortIdentifier());
        $couchDbClient = $this->storage->getDatabase()->getConnection();
        $map = $couchDbClient->getView(NULL, $designDoc, $viewKey, $viewParams);
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
            $fileIsImage = (0 === strpos($asset->getMimeType(), 'image'));

            if ($fileIsImage && TRUE === $this->getConfig()->get('enable_aoi', FALSE))
            {
                $this->writeAoi($asset);
            }

            $metaData = $asset->getMetaData();
            $filePath = $asset->getFullPath();

            $assetData = array(
                'identifier' => "asset-" . $asset->getIdentifier(),
                'data' => base64_encode(fread(fopen($filePath, 'r'), $asset->getSize())),
                'mime' => $asset->getMimeType(),
                'size' => $asset->getSize(),
                'filename' => $asset->getFullName(),
                'modified' => date(DATE_ISO8601, filemtime($filePath)),
                'copyright' => isset($metaData['copyright']) ? $metaData['copyright'] : '',
                'copyrightUrl' => isset($metaData['copyright_url']) ? $metaData['copyright_url'] : '',
                'caption' => isset($metaData['caption']) ? $metaData['caption'] : '',
                'type' => 'asset'
            );

            if ($fileIsImage)
            {
                $imagine = new Imagine\Gd\Imagine();
                $image = $imagine->open($filePath);
                $size = $image->getSize();

                $assetData['width'] = $size->getWidth();
                $assetData['height'] = $size->getHeight();
            }
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
}
