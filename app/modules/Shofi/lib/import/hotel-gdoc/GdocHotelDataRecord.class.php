<?php

/**
 * The GdocHotelDataRecord class is a concrete implementation of the ShofiDataRecord base class.
 * It provides handling for hotel data coming from a gdocs spreadsheet.
 *
 * @version         $Id$
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

        $coreItem = ShofiCoreItem::fromArray($commonData);
        $salesItem = ShofiSalesItem::fromArray(array());
        $detailItem = ShofiDetailItem::fromArray(array(
            'text' => $data['description'],
            'teaser' => $this->extractTeaserText($data['description']),
            'keywords' => $this->mapKeywords($data),
            'attributes' => $this->mapAttributes($data),
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

    protected function mapKeywords(array $data)
    {
        static $keyowrdWhitelist = array(
            'gay-friendly', 'wellness', 'restaurant', 
            'pkw-parkplatz', 'garage', 'bus-parkplatz', 'designhotel'
        );
        $keywords = array();
        if (($classification = $this->mapClassification($data, FALSE)))
        {
            $keywords[] = $classification;
        }
        if (isset($data['furniture']))
        {
            foreach (explode("¦", $data['furniture']) as $value)
            {
                $matchVal = strtolower(trim($value));
                if (in_array($matchVal, $keyowrdWhitelist))
                {
                    $keywords[] = trim($value);
                }
            }
        }
        return $keywords;
    }

    protected function mapClassification(array $data, $extras = TRUE)
    {
        $basicClass = isset($data['stars']) ? $data['stars'] : '';
        $extraClass = isset($data['stars-plus']) ? $data['stars-plus'] : '';
        $classification = NULL;
        if (! empty($basicClass))
        {
            $stars = mb_substr_count($basicClass, '*');
            $suffix = (1 == $stars) ? 'Stern' : 'Sterne';
            if (! $extras)
            {
                $extraClass = '';
            }
            $classification = trim(sprintf('%s-%s %s', $stars, $suffix, $extraClass));
        }
        return $classification;
    }

    protected function mapAttributes(array $data)
    {
        static $attributeNames = array(
            'leisure' => 'Freizeitangebote', 
            'furniture' => 'Hausausstattung', 
            'interior' => 'Zimmerausstattung', 
            'pay-method' => 'Akzeptierte Zahlungsarten', 
            'location' => 'Lage des Hotels',
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
            $values = array();
            foreach (explode("¦", $data[$attributeName]) as $value)
            {
                $value = trim($value);
                if (! empty($value))
                {
                    $values[] = $value;
                }
            }
            if (! empty($values))
            {
                $attributes[] = array('name' => $translatedName, 'values' => $values);
            }
        }
        if (($classification = $this->mapClassification($data)))
        {
            $attributes[] = array('name' => "Klassifizierung", 'values' => array($classification));
        }
        return $attributes;
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
