<?php

/**
 * The WkgDataRecord class is a concrete implementation of the ShofiDataRecord base class.
 * It provides handling for wkg xml data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import/Wkg
 */
class WkgDataRecord extends ShofiDataRecord
{
    /**
     * Holds the name of the property for the class heading id.
     */
    const PROP_CLASS_ID = 'classHeadingId';

    /**
     * Holds the name of the property that holds the import action.
     * Possible values are: E, D, U
     */
    const PROP_ACTION = 'action';

    /**
     * Holds the name of the location class (category).
     *
     * @var string
     */
    protected $classHeadingId;

    /**
     * Holds the action type.
     *
     * @var string
     */
    protected $action;

    public function getAction()
    {
        return $this->action;
    }

    public function getClassHeadingId()
    {
        return $this->classHeadingId;
    }

    public function toArray()
    {
        $values = parent::toArray();
        $values[self::PROP_LOCATION] = $this->getLocation()->toArray();
        return $values;
    }

    /**
     * Return an array holding property names of properties,
     * which we want to expose through our IDataRecord::toArray() method.
     *
     * @return      array
     */
    protected function getExposedProperties()
    {
        return array_merge(
            parent::getExposedProperties(),
            array(
                self::PROP_CLASS_ID,
                self::PROP_ACTION
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
        $craur = Craur::createFromXml($data);

        $values = $this->fetchCraurValues($craur, array(
            self::PROP_IMPORT_IDENTIFIER => 'Listing.ListingID',
            self::PROP_COMPANY => 'Listing.CompanyName',
            self::PROP_FIRST_NAME => 'Listing.FirstName',
            self::PROP_LAST_NAME => 'Listing.LastName',
            self::PROP_TITLE => 'Listing.Title',
            self::PROP_NAME_PREF => 'Listing.NamePrefix',
            self::PROP_HIST_NAME_PREF => 'Listing.HistNameAffix',
            self::PROP_MISC_NAME_PREF => 'Listing.MiscNameAffix',
            self::PROP_PHONE => 'Listing.Phone',
            self::PROP_FAX => 'Listing.Fax',
            self::PROP_MOBILE => 'Listing.Mobile',
            self::PROP_EMAIL => 'Listing.Email',
            self::PROP_WEBSITE => 'Listing.Web',
            self::PROP_CLASS_ID => 'Listing.ClassHeadingId',
            self::PROP_ACTION => 'Listing.Action'
        ));

        $values['location'] = ItemLocation::fromArray(
            $this->fetchCraurValues($craur, array(
                'housenumber' => 'Listing.HouseNo',
                'street' => 'Listing.Street',
                'city' => 'Listing.City',
                'postal_code' => 'Listing.Zip'
            ))
        );

        $values[self::PROP_IMPORT_IDENTIFIER] = $this->buildImportIdentifier($values[self::PROP_IMPORT_IDENTIFIER]);
        
        return $values;
    }

    protected function fetchCraurValues(Craur $craur, $paths)
    {
        $defaultValues = array();
        foreach (array_keys($paths) as $key)
        {
            $defaultValues[$key] = NULL;
        }
        return $craur->getValues($paths, $defaultValues);
    }
}

?>
