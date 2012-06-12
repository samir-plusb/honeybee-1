<?php

/**
 * The PrototypeDataRecord class is a concrete implementation of the ShofiDataRecord base class.
 * It provides handling for mapping data coming from the prototype into the local shofi record format.
 *
 * @version         $Id: WkgDataRecord.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import/Prototype
 */
class PrototypeDataRecord extends ShofiDataRecord
{
    const PROP_MONGO_ID = 'mongoId';

    const PROP_DETAIL_ITEM = 'detailItem';

    const PROP_SALES_ITEM = 'salesItem';

    const PROP_CORE_ITEM = 'coreItem';

    const PROP_LOCATION = 'location';

    const PROTOTYPE_BASEHREF = 'http://prodmgm.berlinonline.de/';

    protected $detailItem;

    protected $salesItem;

    protected $coreItem;

    protected $location;

    protected $mongoId;

    public function getDetailItem()
    {
        return $this->detailItem;
    }

    public function getSalesItem()
    {
        return $this->salesItem;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getCoreItem()
    {
        return $this->coreItem;
    }

    public function getMongoId()
    {
        return $this->mongoId;
    }

    protected function getExposedProperties()
    {
        return array_merge(
            parent::getExposedProperties(),
            array(
                self::PROP_MONGO_ID,
                self::PROP_DETAIL_ITEM,
                self::PROP_SALES_ITEM,
                self::PROP_LOCATION,
                self::PROP_CORE_ITEM
            )
        );
    }

    /**
     * Map the incoming prototype style (array)data into the local shofi format.
     *
     * @param string $data
     *
     * @return array
     */
    protected function parseData($data)
    {
        $masterData = isset($data['Master']) ? $data['Master'] : array();
        $detailData = isset($data['Detail']) ? $data['Detail'] : array();
        $salesData = isset($data['Sales']) ? $data['Sales'] : array();
        $sourceData = isset($data['Source']) ? $data['Source'] : array();
        $mongoId = $data['id'];
        $locationData = isset($masterData['Location']) ? $masterData['Location'] : array();
        $districts = isset($locationData['Districts']) ? $locationData['Districts'] : array();
        // data shared by the coreItem and the masterRecord
        $commonData = array(
            self::PROP_COMPANY => ! empty($masterData['CompanyName']) ? $masterData['CompanyName'] : NULL,
            self::PROP_TITLE => ! empty($masterData['Title']) ? $masterData['Title'] : NULL,
            self::PROP_FIRST_NAME => ! empty($masterData['FirstName']) ? $masterData['FirstName'] : NULL,
            self::PROP_LAST_NAME => ! empty($masterData['LastName']) ? $masterData['LastName'] : NULL,
            self::PROP_PHONE => ! empty($masterData['Phone']) ? $masterData['Phone'] : NULL,
            self::PROP_FAX => ! empty($masterData['Fax']) ? $masterData['Fax'] : NULL,
            self::PROP_MOBILE => ! empty($masterData['Mobile']) ? $masterData['Mobile'] : NULL,
            self::PROP_EMAIL => ! empty($masterData['Email']) ? $masterData['Email'] : NULL,
            self::PROP_WEB => NULL,
            self::PROP_LOCATION => array(
                'street' => ! empty($locationData['Street']) ? $locationData['Street'] : NULL,
                'housenumber' => ! empty($locationData['HouseNo']) ? $locationData['HouseNo'] : NULL,
                'city' => ! empty($locationData['City']) ? $locationData['City'] : NULL,
                'postalCode' => ! empty($locationData['Zip']) ? $locationData['Zip'] : NULL,
                'administrativeDistrict' => ! empty($districts['AdministrativeDistrict'])
                    ? $districts['AdministrativeDistrict'] : NULL,
                'district' => ! empty($districts['District']) ? $districts['District'] : NULL,
                'neighborhood' => ! empty($districts['Neighborhood']) ? $districts['Neighborhood'] : NULL,
                'name' => ! empty($locationData['Name']) ? $locationData['Name'] : NULL,
                'details' => ! empty($locationData['AdditionalDetails']) ? $locationData['AdditionalDetails'] : NULL,
                'coordinates' => array(
                    'lon' => ! empty($locationData['Longitude']) ? $locationData['Longitude'] : NULL,
                    'lat' => ! empty($locationData['Latitude']) ? $locationData['Latitude'] : NULL
                )
            )
        );
        // coreData to coreItem data mapping
        $coreItem = $commonData;
        $coreItem['name'] = ! empty($masterData['Name']) ? $masterData['Name'] : NULL;
        // detailData to detailItem data mapping
        $openingTimes = ! empty($detailData['OpeningTimes']) ? $detailData['OpeningTimes'] : array();
        $detailItemOpenTimes = array();
        foreach ($openingTimes as $openingTimespan)
        {
            $detailItemOpenTimes[] = array(
                'from' => $openingTimespan['from'],
                'to' => $openingTimespan['till']
            );
        }
        $detailItem = array(
            'teaser' => ! empty($detailData['Teaser']) ? $detailData['Teaser'] : NULL,
            'text' => ! empty($detailData['Text']) ? $detailData['Text'] : NULL,
            'openingTimes' => $detailItemOpenTimes,
            'attributes' => ! empty($detailData['Attributes']) ? $detailData['Attributes'] : array(),
            'keywords' => ! empty($detailData['SearchableKeywords']) ? $detailData['SearchableKeywords'] : array(),
            'attachments' => $this->processAttachments(
                ! empty($detailData['Attachments']) ? $detailData['Attachments'] : array()
            ),
            'category' => ! empty($masterData['Category']) ? $masterData['Category'] : NULL,
            'additionalCategories' => ! empty($masterData['AdditionalCategories']) ? $masterData['AdditionalCategories'] : array()
        );
        // salesData to salesItem data mapping
        $salesItem = array(
            'product' => ! empty($salesData['Product']) ? $salesData['Product'] : NULL,
            'expireDate' => ! empty($salesData['ExpirationDate']) ? $salesData['ExpirationDate'] : NULL,
            'teaser' => ! empty($salesData['Teaser']) ? $salesData['Teaser'] : NULL,
            'text' => ! empty($salesData['Text']) ? $salesData['Text'] : NULL,
            'attributes' => ! empty($salesData['Attributes']) ? $salesData['Attributes'] : array(),
            'keywords' => ! empty($salesData['SearchableKeywords']) ? $salesData['SearchableKeywords'] : array(),
            'attachments' => $this->processAttachments(
                ! empty($salesData['Attachments']) ? $salesData['Attachments'] : array()
            ),
            'additionalCategories' => ! empty($salesData['AdditionalCategories']) ? $salesData['AdditionalCategories'] : array()
        );

        // masterRecord data
        return array_merge(
            $commonData,
            array(
                self::PROP_IDENT => isset($sourceData['ListingId']) ? $sourceData['ListingId'] : $mongoId,
                self::PROP_NAME_PREF => NULL, // not provided by prototype
                self::PROP_HIST_NAME_PREF => NULL,  // not provided by prototype
                self::PROP_MISC_NAME_PREF => NULL,  // not provided by prototype
                self::PROP_MONGO_ID => $mongoId,
                self::PROP_CORE_ITEM => $coreItem,
                self::PROP_DETAIL_ITEM =>  $detailItem,
                self::PROP_SALES_ITEM => $salesItem
            )
        );
    }

    protected function processAttachments(array $attachments)
    {
        $assetService = ProjectAssetService::getInstance();
        $imagine = new Imagine\Gd\Imagine();
        $processedAttachments = array();
        foreach ($attachments as $attachment)
        {
            try
            {
                $asset = $assetService->put(self::PROTOTYPE_BASEHREF.$attachment['url']);
                $filePath = $asset->getFullPath();
                $image = $imagine->open($filePath);
                $size = $image->getSize();
                $metaData = array(
                    'width' => $size->getWidth(),
                    'height' => $size->getHeight(),
                    'copyright' => isset($attachment['copyright']) ? $attachment['copyright'] : '',
                    'copyright_url' => isset($attachment['copyright_url']) ? $attachment['copyright_url'] : '',
                    'caption' => isset($attachment['caption']) ? $attachment['caption'] : ''
                );
                $assetService->update($asset, $metaData);
                $metaData['filename'] = $asset->getFullName();
                $processedAttachments[] = $asset->getIdentifier();
            }
            catch(Exception $e)
            {
                error_log("[".__METHOD__."] Error while procssing attachment, " . $e->getMessage());
                continue;
            }
        }
        return $processedAttachments;
    }
}

?>