<?php

/**
 * The HotelDataRecord class is a concrete implementation of the ShofiDataRecord base class.
 * It provides handling for movies/hotel xml data.
 *
 * @version         $Id: HotelDataRecord.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import/Hotel
 */
class BtkHotelDataRecord extends ShofiDataRecord
{
    const CATEGORY_SRC = 'hotels-btk';

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

    protected function parseData($data)
    {
        $parsedStreet = ItemLocation::parseStreet($data['address']['street']);

        // data shared between the masterRecord and the coreItem.
        $commonData = array(
            self::PROP_COMPANY => $data['address']['company'],
            self::PROP_LOCATION => ItemLocation::fromArray(array(
                'name' => $data['name'],
                'street' => $parsedStreet['street'],
                'housenumber' => $parsedStreet['number'],
                'details' => $parsedStreet['detail'],
                'city' => $data['address']['city'],
                'postalCode' => $data['address']['uzip'],
                'district' => $data['address']['district'],
                'coordinates' => $data['address']['coords']
            ))
        );
        $attributes = array();
        foreach ($data['features'] as $feature => $values)
        {
            if (! empty($values))
            {
                $attributes[] = array('name' => $feature, 'values' => $values);
            }
        }
        $keywords = array();
        if (isset($data['features']['stars']))
        {
            $keywords[] = reset($data['features']['stars']) . '-Sterne';
        }
        $coreItem = ShofiCoreItem::fromArray($commonData);
        $salesItem = ShofiSalesItem::fromArray(array());
        $detailItem = ShofiDetailItem::fromArray(array(
            'text' => $data['booking-info'] . PHP_EOL . 
                $data['check-in-out'] . PHP_EOL . 
                $data['conditionsextras'],
            'teaser' => $data['description'],
            'keywords' => $keywords,
            'attributes' => $attributes,
            'attachments' => $this->processAttachments($data['images'])
        ));

        $categorySource = 'empty';
        return array_merge(
            $commonData,
            array(
                self::PROP_IMPORT_IDENTIFIER => $this->buildImportIdentifier($data['identifier']),
                self::PROP_CORE_ITEM => $coreItem,
                self::PROP_DETAIL_ITEM => $detailItem,
                self::PROP_SALES_ITEM => $salesItem,
                self::PROP_CATEGORY_SRC => 'hotels-btk:' . $categorySource
            )
        );
    }

    protected function processAttachments(array $attachments)
    {
        static $baseUrl = 'http://hotel.berlin.de';

        $assetService = ProjectAssetService::getInstance();
        $assetIds = array();
        foreach ($attachments as $image)
        {
            $imageUri = ! preg_match('#^http[s]*:[\/]{1,2}#is', $image['uri']) ? ($baseUrl.$image['uri']) : $image['uri'];
            try
            {
                $asset = $assetService->put($imageUri);
                $metaData = array(
                    'filename' => $asset->getFullName(),
                    'width' => $image['width'],
                    'height' => $image['height'],
                    'copyright' => 'Telavision'
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
