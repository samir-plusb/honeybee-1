<?php

/**
 * The HotelDataRecord class is a concrete implementation of the ShofiDataRecord base class.
 * It provides handling for movies/hotel xml data.
 *
 * @version         $Id$
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
        $internalKeywords = $this->mapInternalKeywords($data);
        $coreItem = ShofiCoreItem::fromArray($commonData);
        $salesItem = ShofiSalesItem::fromArray(array());
        $detailItem = ShofiDetailItem::fromArray(array(
            'text' => isset($data['description']) ? $data['description'] : '',
            'teaser' => isset($data['description']) ? 
                $this->extractTeaserText($data['description']) : '',
            'keywords' => $this->mapKeywords($data, $internalKeywords),
            'attributes' => $this->mapAttributes($data),
            'attachments' => isset($data['images']) ? 
                $this->processAttachments($data['images']) : array()
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

    protected function extractTeaserText($text)
    {
        $sentences = preg_split('/\.\s+/is', $text);
        if (0 < count($sentences))
        {
            if (! (substr($sentences[0], -1) === '.'))
            {
                $sentences[0] .= '.';
            }
            return $sentences[0];
        }
        return NULL;
    }

    protected function mapKeywords(array $data, array $internalKeywords)
    {
        // somehow try to match some relavant keywords
        static $keywordWhitelist = array(
            'gay', 'friendly', 'wellness', 'restaurant', 
            'parkplatz', 'garage', 'designhotel'
        );
        $keywords = array();
        if (($classification = $this->mapClassification($data, FALSE)))
        {
            $keywords[] = $classification;
        }
        foreach ($data['features'] as $values)
        {
            foreach ($values as $value)
            {
                foreach ($keywordWhitelist as $keyword)
                {
                    $testVal = strtolower($value);
                    if (FALSE !== strpos($testVal, $keyword) && ! in_array($value, $keywords))
                    {
                        $keywords[] = $value;
                    }
                }
            }
        }
        foreach ($internalKeywords as $value)
        {
            foreach ($keywordWhitelist as $keyword)
            {
                $testVal = strtolower($value);
                if (FALSE !== strpos($testVal, $keyword) && ! in_array($value, $keywords))
                {
                    $keywords[] = $value;
                }
            }
        }
        return $keywords;
    }

    protected function mapAttributes(array $data)
    {
        static $attributeNames = array(
            'booking-info' => 'Reservierungsinfo',
            'check-in-out' => 'Anreise / Abreise'
        );
        $attributes = array();
        foreach ($attributeNames as $attributeName => $translatedName)
        {
            if (! isset($data[$attributeName]))
            {
                continue;
            }
            $attributes[] = array('name' => $translatedName, 'values' => array($data[$attributeName]));
        }
        foreach ($data['features'] as $key => $values)
        {
            if ($key !== 'stars-plus' && $key !== 'stars' && ! empty($values))
            {
                $attributes[] = array('name' => $key, 'values' => $values);
            }
        }
        if (($classification = $this->mapClassification($data)))
        {
            $attributes[] = array('name' => "Klassifizierung", 'values' => array($classification));
        }
        return $attributes;
    }

    protected function mapClassification(array $data, $extras = TRUE)
    {
        $basicClass = isset($data['features']['stars']) ? $data['features']['stars'][0] : '';
        $extraClass = isset($data['features']['stars-plus']) ? $data['features']['stars-plus'][0] : '';
        $classification = NULL;
        if (! empty($basicClass))
        {
            $suffix = (1 == $basicClass) ? 'Stern' : 'Sterne';
            if (! $extras)
            {
                $extraClass = '';
            }
            $classification = trim(sprintf('%s-%s %s', $basicClass, $suffix, $extraClass));
        }
        return $classification;
    }

    protected function mapInternalKeywords(array $data)
    {
        $keywords = array();
        foreach ($data['features'] as $key => $values)
        {
            if (empty($values))
            {
                $keywords[] = $key;
            }
        }
        return $keywords;
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
                    'height' => $image['height']
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
