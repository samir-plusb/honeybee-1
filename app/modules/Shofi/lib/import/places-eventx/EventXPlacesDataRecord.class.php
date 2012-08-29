<?php

/**
 * The EventsXPlacesDataRecord class is a concrete implementation of the ShofiDataRecord base class.
 * It provides handling for movies/hotel xml data.
 *
 * @version         $Id: EventsXPlacesDataRecord.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import/Hotel
 */
class EventXPlacesDataRecord extends ShofiDataRecord
{
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

    protected function getAttributes()
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
        // data shared between the masterRecord and the coreItem.
        $commonData = array(
            self::PROP_COMPANY => $data['name'],
            self::PROP_PHONE => $data['telefon'],
            self::PROP_FAX => $data['fax'],
            self::PROP_WEBSITE => $data['website'],
            self::PROP_EMAIL => $data['email'],
            self::PROP_LOCATION => ItemLocation::fromArray(array(
                'name' => $data['name'],
                'street' => $data['address']['street'],
                'housenumber' => $data['address']['houseNumber'],
                'city' => $data['address']['city'],
                'postalCode' => $data['address']['uzip'],
                'district' => $data['address']['district']
            ))
        );
        $keywords = array('Veranstaltungsort');
        foreach ($data['keywords'] as $keyword)
        {
            $keywords[] = $keyword;
        }
        
        $coreItem = ShofiCoreItem::fromArray($commonData);
        $salesItem = ShofiSalesItem::fromArray(array());
        $detailItem = ShofiDetailItem::fromArray(array(
            'keywords' => $keywords,
            'openingTimes' => $data['opening_times']
        ));
        $categorySrc = trim(strtolower(str_replace('/', ' ', $data['category'])));
        list($category, $subcategory) = $this->mapCategory($categorySrc);

        $parsedData = array_merge(
            $commonData,
            array(
                self::PROP_IMPORT_IDENTIFIER => $this->buildImportIdentifier(
                    str_replace('adr_', '', $data['identifier'])
                ),
                self::PROP_CORE_ITEM => $coreItem,
                self::PROP_DETAIL_ITEM => $detailItem,
                self::PROP_SALES_ITEM => $salesItem,
                self::PROP_CATEGORY_SRC => 'places-eventx:' . $data['category'],
                self::PROP_ATTRIBUTES => array(
                    'tip-category' => $category,
                    'tip-subcategory' => $subcategory,
                    'filemaker-id' => $data['filemaker_id'],
                    'public-transport' => $data['public_transport']
                )
            )
        );

        // return mapped import data matching the format expected by the ShofiDataImport.
        return $parsedData;
    }

    protected function mapCategory($categorySrc)
    {
        $category = NULL;
        $subcategory = NULL;

        if (preg_match("#(kino)#", $categorySrc))
        {
            $category = 'Kino & Film';
        }
        elseif (preg_match("#(diskotheken|club|musikkneipen)#", $categorySrc))
        {
            $category = 'Musik & Party';
            $subcategory = 'Clubs';
        }
        elseif (preg_match("#(klassik|konzert|lounge|musiklocation|location|open air|partylocations)#", $categorySrc))
        {
            $category = 'Musik & Party';
        }
        elseif (preg_match("#(galerien|museen|tip-andereorte)#", $categorySrc))    
        {
            $category = 'Kultur & Freizeit';
            $subcategory = 'Kunst';
        }   
        elseif (preg_match("#(sportanlagen)#", $categorySrc))
        {
            $category = 'Kultur & Freizeit';
            $subcategory = 'Sport';
        }
        elseif (preg_match("#(kabarett|theater)#", $categorySrc))
        {
            $category = 'Kultur & Freizeit';
            $subcategory = 'Theater & Bühne';
        }
        elseif (preg_match("#(literatur)#", $categorySrc))
        {
            $category = 'Kultur & Freizeit';
            $subcategory = 'Lesungen & Bücher';
        }
        elseif (preg_match("#(führungen|kirchen|kultur|sexclubs|sonstige|tanzschulen)#", $categorySrc))
        {
            $category = 'Kultur & Freizeit';
        }
        elseif (preg_match("#(gastro)#", $categorySrc))
        {
            $category = 'Essen & Trinken';
        }
        elseif (preg_match("#(ärzte|sonstige)#", $categorySrc))
        {
            $category = 'Sonstige';
        }

        return array($category, $subcategory);
    }
}

?>
