<?php

/**
 * The ContentMachineHttpExport is responseable for syncing places and catgories to the contentmachine.
 *
 * @version         $Id: ContentMachineHttpExport.class.php 1009 2012-03-02 19:01:55Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 */
class ContentMachineHttpExport
{
    const SETTING_EXPORT_URL = 'shofi.cm_export_url';

    const SETTING_EXPORT_ENABLED = 'shofi.cm_export_enabled';

    protected $contentMachineUrl;

    protected $lastErrors = array();

    public function __construct($contentMachineUrl)
    {
        $this->contentMachineUrl = $contentMachineUrl;
    }

    public function exportShofiPlace(ShofiWorkflowItem $entity)
    {
        $data = $entity->toArray();
        $cmDataKeys = array(
                'coreItem' => 'coreData',
                'salesItem' => 'salesData',
                'detailItem' => 'detailData',
                'attributes' => 'attributes',
                'lastModified' => 'lastModified'
                );

        $data['detailItem']['attachments'] = $this->prepareContentMachineAssetData(
            $entity->getDetailItem()->getAttachments()
        );
        $data['salesItem']['attachments'] = $this->prepareContentMachineAssetData(
            $entity->getSalesItem()->getAttachments()
        );

        $cmData = array();
        foreach ($cmDataKeys as $localKey => $cmKey)
        {
            $cmData[$cmKey] = $data[$localKey];
        }
        $cmData['mongoId'] = $entity->getAttribute('mongoId');

        return $this->issueCurlRequest(
            $this->flattenData(
                array('place' => $cmData, 'id' => $entity->getIdentifier(), 'action' => 'write')
            )
        );
    }

    public function exportShofiCategory(ShofiCategoriesWorkflowItem $entity)
    {
        $categoryData = $entity->getMasterRecord()->toArray();
        $categoryData['attachments'] = $this->prepareContentMachineAssetData(
            $entity->getMasterRecord()->getAttachments()
        );
        return $this->issueCurlRequest(
            $this->flattenData(
                array('category' => $categoryData, 'id' => $entity->getIdentifier(), 'action' => 'write')
            )
        );
    }

    public function exportShofiVertical(ShofiVerticalsWorkflowItem $entity)
    {
        $vertical = $entity->getMasterRecord();
        $verticalImages = $this->prepareContentMachineAssetData(
            $vertical->getImages()
        );
        $postData = array(
            'id' => $entity->getIdentifier(),
            'name' => $vertical->getName(),
            'portals' => array(
                'berlin.de' => array(
                    'uri' => $vertical->getUrl(),
                    'priority' => 100
                )
            ),
            'categories' => $vertical->getCategories(),
            'teaser' => $vertical->getTeaser(),
            'teaser_img' => $verticalImages
        );
        return $this->issueCurlRequest(
            $this->flattenData(
                array('vertical' => $postData, 'id' => $entity->getIdentifier(), 'action' => 'write')
            )
        );
    }

    public function deleteEntity($identifier, $type)
    {
        return $this->issueCurlRequest(
            $this->flattenData(
                array('id' => $identifier, 'type' => $type, 'action' => 'delete')
            )
        );
    }

    public function getLastErrors()
    {
        return $this->lastErrors;
    }

    public function hasErrors()
    {
        return ! empty($this->lastErrors);
    }

    protected function flattenData(array $data)
    {
        $flatData = array();
        foreach (AgaviArrayPathDefinition::getFlatKeyNames($data) as $dataPath)
        {
            $arrayPath = new AgaviVirtualArrayPath($dataPath);
            $flatData[$dataPath] = $arrayPath->getValue($data, '');
        }
        return $flatData;
    }

    /**
     *
     */
    protected function issueCurlRequest(array $postData)
    {
        error_log(
            "[".__CLASS__ . "] Sending the following to the contentmachine shofi frontend: " . PHP_EOL . print_r($postData, TRUE)
        );
		$this->logInfo(
            "Sending the following to the contentmachine shofi frontend: " . PHP_EOL . print_r($postData, TRUE)
        );

        $curl = ProjectCurl::create($this->contentMachineUrl);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_ENCODING, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Accept: application/json; charset=UTF-8',
            'Expect:' // Prevent lighttpd from bailing out. @see http://redmine.lighttpd.net/issues/1017
        ));
        $response = curl_exec($curl);
        $respCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (200 !== $respCode)
        {
            $this->lastErrors[] = curl_error($curl);
            return FALSE;
        }

        $resp = json_decode($response, TRUE);
        if (! isset($resp['state']) || 'success' !== $resp['state'])
        {
            $this->lastErrors = $resp['errors'];
            return FALSE;
        }
        return TRUE;
    }

    protected function prepareContentMachineAssetData(array $assetIds)
    {
        $assets = array();
        foreach (ProjectAssetService::getInstance()->multiGet($assetIds) as $id => $asset)
        {
            $metaData = $asset->getMetaData();
            $imagine = new Imagine\Gd\Imagine();
            $filePath = $asset->getFullPath();
            $image = $imagine->open($filePath);
            $size = $image->getSize();
            $assets[] = array(
                'id' => $asset->getIdentifier(),
                'width' => $size->getWidth(),
                'height' => $size->getHeight(),
                'mime' => $asset->getMimeType(),
                'filename' => $asset->getFullName(),
                'modified' => date(DATE_ISO8601, filemtime($filePath)),
                'copyright' => isset($metaData['copyright']) ? $metaData['copyright'] : '',
                'copyright_url' => isset($metaData['copyright_url']) ? $metaData['copyright_url'] : '',
                'caption' => isset($metaData['caption']) ? $metaData['caption'] : ''
            );
        }
        return $assets;
    }

	protected function logInfo($msg)
    {
        $logger = AgaviContext::getInstance()->getLoggerManager()->getLogger('app');
        $infoMsg = sprintf("[%s] %s", get_class($this), $msg);
        $logger->log(
            new AgaviLoggerMessage($infoMsg, AgaviLogger::INFO)
        );
    }
}

?>
