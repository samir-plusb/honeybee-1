<?php

/**
 * The GdocHotelDataRecord class is a concrete implementation of the ShofiDataRecord base class.
 * It provides handling for hotel data coming from a gdocs spreadsheet.
 *
 * @version         $Id: GdocHotelDataRecord.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import/Hotel-Gdoc
 */
class GdocHotelDataRecord extends ShofiDataRecord
{
    const CATEGORY_SRC = 'hotels-gdoc';

    const PROP_DETAIL_ITEM = 'detailItem';

    const PROP_SALES_ITEM = 'salesItem';

    const PROP_CORE_ITEM = 'coreItem';

    const PROP_ATTRIBUTES = 'attributes';

    protected $detailItem;

    protected $salesItem;

    protected $coreItem;

    protected $attributes = array();

    public function toArray()
    {
        $values = parent::toArray();
        $values[self::PROP_LOCATION] = $this->getLocation()->toArray();
        $values[self::PROP_CORE_ITEM] = $this->getCoreItem()->toArray();
        $values[self::PROP_DETAIL_ITEM] = $this->getDetailItem()->toArray();
        $values[self::PROP_SALES_ITEM] = $this->getSalesItem()->toArray();
        return $values;
    }

    public function getDetailItem()
    {
        return $this->detailItem;
    }

    public function getSalesItem()
    {
        return $this->salesItem;
    }

    public function getCoreItem()
    {
        return $this->coreItem;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    protected function getExposedProperties()
    {
        return array_merge(
            parent::getExposedProperties(),
            array(
                self::PROP_DETAIL_ITEM,
                self::PROP_SALES_ITEM,
                self::PROP_CORE_ITEM,
                self::PROP_ATTRIBUTES
            )
        );
    }

    /**
     * @todo handle images
     */
    protected function parseData($data)
    {
        $parsedStreet = ItemLocation::parseStreet($data['street-no']);
        // data shared between the masterRecord and the coreItem.
        $commonData = array(
            self::PROP_COMPANY => $data['name'],
            self::PROP_LOCATION => ItemLocation::fromArray(array(
                'name' => $data['name'],
                'street' => $parsedStreet['street'],
                'housenumber' => $parsedStreet['number'],
                'details' => $parsedStreet['detail'],
                'city' => $data['city'],
                'postalCode' => $data['zip'],
                'district' => $data['district']
            ))
        );

        $attributeNames = array(
            'stars', 'stars-plus', 'leisure', 'furniture', 
            'interior', 'pay-method', 'location'
        );
        $attributes = array();
        foreach ($attributeNames as $attribute)
        {
            $values = array();
            foreach (explode("¦", $data[$attribute]) as $value)
            {
                $value = trim($value);
                if (! empty($value))
                {
                    $values[] = $value;
                }
            }
            if (! empty($values))
            {
                $attributes[] = array('name' => $attribute, 'values' => $values);
            }
        }
        $keywords = array();
        if (isset($data['stars']) && ($starCount = mb_substr_count($data['stars'], '*')))
        {
            $keywords[] = $starCount . '-Sterne';
        }
        $coreItem = ShofiCoreItem::fromArray($commonData);
        $salesItem = ShofiSalesItem::fromArray(array());
        $detailItem = ShofiDetailItem::fromArray(array(
            'text' => $data['booking-info'] . PHP_EOL . $data['check-in-out'],
            'teaser' => $data['description'],
            'keywords' => $keywords,
            'attributes' => $attributes,
            'attachments' => $this->processAttachments($data['images'])
        ));

        $categorySource = 'empty';
        return array_merge(
            $commonData,
            array(
                self::PROP_IMPORT_IDENTIFIER => $this->buildImportIdentifier($data['id']),
                self::PROP_CORE_ITEM => $coreItem,
                self::PROP_DETAIL_ITEM => $detailItem,
                self::PROP_SALES_ITEM => $salesItem,
                self::PROP_CATEGORY_SRC => 'hotels-gdoc:' . $categorySource
            )
        );
    }

    protected function processAttachments($attachments)
    {
        static $baseUrl = 'http://hotel.berlin.de';
        
        $assetService = ProjectAssetService::getInstance();
        $imagine = new Imagine\Gd\Imagine();

        $assetIds = array();
        foreach (explode("¦", $attachments) as $imageUri)
        {
            $imageUri = ! preg_match('#^http[s]*:[\/]{1,2}#is', $imageUri) ? ($baseUrl.$imageUri) : $imageUri;
            try
            {
                $asset = $assetService->put($imageUri);
                $filePath = $asset->getFullPath();
                $image = $imagine->open($filePath);
                $size = $image->getSize();
                $metaData = array(
                    'filename' => $asset->getFullName(),
                    'width' => $size->getWidth(),
                    'height' => $size->getHeight(),
                    'copyright' => isset($attachment['copyright']) ? $attachment['copyright'] : '',
                    'copyright_url' => isset($attachment['copyright_url']) ? $attachment['copyright_url'] : '',
                    'caption' => isset($attachment['caption']) ? $attachment['caption'] : ''
                );
                $assetService->update($asset, $metaData);
                $assetIds[] = $asset->getIdentifier();
            }
            catch(Exception $e)
            {
                error_log("[".__METHOD__."] Error while procssing attachment, " . $e->getMessage());
                continue;
            }
        }
        return $assetIds;
    }
}

?>
