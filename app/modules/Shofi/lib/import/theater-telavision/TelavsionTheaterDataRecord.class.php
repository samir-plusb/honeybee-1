<?php

/**
 * The TheaterDataRecord class is a concrete implementation of the ShofiDataRecord base class.
 * It provides handling for movies/theater xml data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import/Theater
 */
class TelavsionTheaterDataRecord extends ShofiDataRecord
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
     * Parse a given wkg listing xml string into the shofi record format.
     *
     * @param string $data
     *
     * @return array
     */
    protected function parseData($data)
    {
        $parsedStreet = ItemLocation::parseStreet($data['location']['street']);
        // data shared between the masterRecord and the coreItem.
        $commonData = array(
            self::PROP_COMPANY => $data['name'],
            self::PROP_PHONE => $data['telefon'],
            self::PROP_WEBSITE => $data['url'],
            self::PROP_LOCATION => ItemLocation::fromArray(array(
                'street' => $parsedStreet['street'],
                'housenumber' => $parsedStreet['number'],
                'details' => $parsedStreet['detail'],
                'city' => $data['location']['city'],
                'postalCode' => $data['location']['postalCode'],
                'district' => $data['location']['district'],
                'name' => $data['name']
            ))
        );

        $coreItem = ShofiCoreItem::fromArray($commonData);
        $salesItem = ShofiSalesItem::fromArray(array());
        $detailItem = ShofiDetailItem::fromArray(array(
            'text' => $data['description']
        ));
        $parsedData = array_merge(
            $commonData,
            array(
                self::PROP_IMPORT_IDENTIFIER => $this->buildImportIdentifier($data['id']),
                self::PROP_CORE_ITEM => $coreItem,
                self::PROP_DETAIL_ITEM => $detailItem,
                self::PROP_SALES_ITEM => $salesItem,
                self::PROP_CATEGORY_SRC => 'theaters-telavision:empty'
            )
        );
        $parsedData[self::PROP_ATTRIBUTES] = array(
            'prices' => $data['prices'],
            'screens' => $data['screens']
        );
        // return mapped import data matching the format expected by the ShofiDataImport.
        return $parsedData;
    }
}

?>
