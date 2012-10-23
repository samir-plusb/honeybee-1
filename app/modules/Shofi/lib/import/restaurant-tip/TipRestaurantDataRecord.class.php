<?php

/**
 * The TheaterDataRecord class is a concrete implementation of the ShofiDataRecord base class.
 * It provides handling for movies/theater xml data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import/Tip
 */
class TipRestaurantDataRecord extends ShofiDataRecord
{
    const PROP_DETAIL_ITEM = 'detailItem';

    const PROP_SALES_ITEM = 'salesItem';

    const PROP_CORE_ITEM = 'coreItem';

    protected $detailItem;

    protected $salesItem;

    protected $coreItem;

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

    protected function getExposedProperties()
    {
        return array_merge(
            parent::getExposedProperties(),
            array(
                self::PROP_DETAIL_ITEM,
                self::PROP_SALES_ITEM,
                self::PROP_CORE_ITEM
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
        $parsedStreet = ItemLocation::parseStreet($data['MDB::Anz_StrasseHausNr']);
        // data shared between the masterRecord and the coreItem.
        $commonData = array(
            self::PROP_COMPANY => $data['MDB::Anz_Name'],
            self::PROP_PHONE => $data['MDB::Telefon'],
            self::PROP_FAX => $data['MDB::Telefax'],
            self::PROP_WEBSITE => $data['MDB::URL'],
            self::PROP_EMAIL => $data['MDB::eMail'],
            self::PROP_LOCATION => ItemLocation::fromArray(array(
                'street' => $parsedStreet['street'],
                'housenumber' => $parsedStreet['number'],
                'details' => $parsedStreet['detail'],
                'city' => $data['MDB::Stadt'],
                'postalCode' => $data['MDB::PLZ'],
                'district' => $data['MDB::Bezirk'],
                'administrativeDistrict' => $data['MDB::Verwaltungsbezirk'],
                'name' => $data['MDB::Anz_Name']
            ))
        );

        $attributes = $this->mapAttributes($data);

        $coreItem = ShofiCoreItem::fromArray($commonData);
        $salesItem = ShofiSalesItem::fromArray(array());
        $detailItem = ShofiDetailItem::fromArray(array(
            'text' => $data['Online_Ausgabe::Online_Text'],
            'teaser' => $this->extractTeaserText($data['Online_Ausgabe::Online_Text']),
            'openingTimes' => $this->parseOpeningTimes($data['gastronomie::Öffnungszeiten']),
            'attributes' => $attributes,
            'attachments' => $this->processAttachments($data['MDB::ID_MDB']),
            'keywords' => $this->mapKeywords($data, $attributes)
        ));

        return array_merge(
            $commonData,
            array(
                self::PROP_IMPORT_IDENTIFIER => $this->buildImportIdentifier($data['MDB::ID_MDB']),
                self::PROP_CORE_ITEM => $coreItem,
                self::PROP_DETAIL_ITEM => $detailItem,
                self::PROP_SALES_ITEM => $salesItem,
                self::PROP_CATEGORY_SRC => $this->mapCategorySource($data)
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

    protected function mapCategorySource(array $data)
    {
        $categorySource = trim($data['gastronomie::Orts_Typ']);
        if (isset($data['gastronomie::Küchenrichtung']))
        {
            $val = trim($data['gastronomie::Küchenrichtung']);
            if (! empty($val))
            {
                $categorySource .= empty($categorySource) ? 
                    '' : ('/' . trim($data['gastronomie::Küchenrichtung']));
            }
        }
        $categorySource = rtrim($categorySource, "/");
        $categorySource = empty($categorySource) ? 'empty' : $categorySource;

        return 'restaurants-tip:' . $categorySource;
    }

    protected function mapAttributes(array $data)
    {
        $attributes = array();
        $drinkPrices = $this->parsePricesList($data['gastronomie::Preise_Getränke']);
        if (! empty($drinkPrices))
        {
            $attributes[] =  array('name' => 'Getränkepreise', 'values' => $drinkPrices);
        }
        $foodPrices = $this->parsePricesList($data['gastronomie::Preise_Speisen']);
        if (! empty($foodPrices))
        {
            $attributes[] =  array('name' => 'Essenspreise', 'values' => $foodPrices);
        }
        $publicTransports = $this->parseCommaSeparatedValues($data['MDB::Aus_Fahrverbindung']);
        if (! empty($publicTransports))
        {
            $attributes[] =  array('name' => 'Fahrverbindungen', 'values' => $publicTransports);
        }
        $nightlyTransports = $this->parseCommaSeparatedValues($data['MDB::Nachtfahrverbindungen']);
        if (! empty($nightlyTransports))
        {
            $attributes[] =  array('name' => 'nächt. Fahrverbindungen', 'values' => $nightlyTransports);
        }
        $rooms = $this->parseCommaSeparatedValues($data['gastronomie::Räumlichkeiten']);
        if (! empty($rooms))
        {
            $attributes[] =  array('name' => 'Räumlichkeiten', 'values' => $rooms);
        }
        $smoking = $this->parseCommaSeparatedValues($data['gastronomie::Raucher']);
        if (! empty($smoking))
        {
            $attributes[] =  array('name' => 'Rauchen', 'values' => $smoking);
        }
        $insideSeats = trim($data['gastronomie::Plätze_innen']);
        if (! empty($insideSeats))
        {
            $attributes[] =  array('name' => 'Sitzplätze innen', 'values' => array($insideSeats));
        }
        $outsideSeats = trim($data['gastronomie::Plätze_aussen']);
        if (! empty($outsideSeats))
        {
            $attributes[] =  array('name' => 'Sitzplätze aussen', 'values' => array($outsideSeats));
        }
        $creditCards = $this->parseCommaSeparatedValues($data['gastronomie::Kreditkarten']);
        if (! empty($creditCards))
        {
            $attributes[] =  array('name' => 'Kreditkarten', 'values' => $creditCards);
        }
        if (isset($data['gastronomie::Küchenrichtung']))
        {
            $attributes[] = array('name' => 'Küchenrichtung', 'values' => array($data['gastronomie::Küchenrichtung']));
        }

        if (isset($data['gastronomie::Öffnungszeiten']))
        {
            $attributes[] = array('name' => 'Öffnungszeiten', 'values' => array($data['gastronomie::Öffnungszeiten']));
        }
        return $attributes;
    }

    protected function mapKeywords(array $data, array $attributes)
    {
        $keywords = array();
        if (isset($data['gastronomie::Küchenrichtung']))
        {
            $val = trim($data['gastronomie::Küchenrichtung']);
            if (! empty($val))
            {
                $keywords[] = "Küche: " . strtolower($data['gastronomie::Küchenrichtung']);
            }
        }
        $rooms = $this->parseCommaSeparatedValues($data['gastronomie::Räumlichkeiten']);
        if (! empty($rooms))
        {
            foreach ($rooms as $val)
            {
                $val = strtolower($val);
                if (FALSE !== strpos($val, 'kinderfreundlich'))
                {
                    $keywords[] = 'kinderfreundlich';
                }
                elseif (FALSE !== strpos($val, 'behindertengerecht'))
                {
                    $keywords[] = 'behindertengerecht';
                }
            }
        }

        return $keywords;
    }

    protected function parsePricesList($pricesString)
    {
        $prices = array();
        $matches = array();
        if (preg_match_all('=(.*€),\s=isU', $pricesString, $matches))
        {
            foreach ($matches[1] as $price)
            {
                $prices[] = str_replace(',', '.', $price);
            }
        }
        return $prices;
    }

    protected function parseCommaSeparatedValues($valueString)
    {
        $values = array();
        $parts = explode(',', $valueString);
        foreach ($parts as $value)
        {
            if (! empty($value))
            {
                $values[] = $value;
            }
        }
        return $values;
    }

    protected function parseOpeningTimes($openinigTimesString)
    {
        $weekDays = array(
            'mo' => 'Montag',
            'di' => 'Dienstag',
            'mi' => 'Mittwoch',
            'do' => 'Donnerstag',
            'fr' => 'Freitag',
            'sa' => 'Samstag',
            'so' => 'Sonntag'
        );
        $openingTimes = array();
        $times = explode(',', $openinigTimesString);
        foreach ($times as $time)
        {
            $matches = array();
            if (preg_match('~(\w\w)\-(\w\w)\s+(\d{1,2})\-(\d{1,2})~is', $time, $matches))
            {
                $fromDay = strtolower($matches[1]);
                $toDay = strtolower($matches[2]);
                $from = array('day' => $weekDays[$fromDay], 'time' => $matches[3] . ':00');
                $to = array('day' => $weekDays[$toDay], 'time' => $matches[4] . ':00');
                $openingTimes[] = array('from' => $from, 'to' => $to);
            }
        }
        return $openingTimes;
    }

    protected function processAttachments($importIdentifier)
    {
        $copyrightProvider = new TipAssetsCopyrightProvider(
            realpath(
                AgaviConfig::get('shofi.tip_import_dir') . DIRECTORY_SEPARATOR . 'fotografen.txt'
            )
        );
        
        $assetIds = array();
        $baseDirectory = realpath(AgaviConfig::get('shofi.tip_import_dir') .
            DIRECTORY_SEPARATOR . $importIdentifier . DIRECTORY_SEPARATOR);

        if (! $baseDirectory)
        {
            return $assetIds;
        }
        $assetPattern = '(?:(?:(\d+)_(.*).(jpg|JPG|png|PNG))|(?:(.*)_(\d+).(jpg|JPG|png|PNG)))';
        $assetService = ProjectAssetService::getInstance();
        $imagine = new Imagine\Gd\Imagine();
        $iterator = new ProjectDirectoryRegexpIterator($baseDirectory, $assetPattern);
        foreach ($iterator as $file)
        {
            try
            {
                $asset = $assetService->put('file://'.$file, array(), FALSE);
                $image = $imagine->open($asset->getFullPath());
                $size = $image->getSize();
                $nameParts = explode('_', $asset->getName());
                $acronym = '';
                if (2 === count($nameParts))
                {
                    if (preg_match('~\d+~is', $nameParts[0]))
                    {
                        $acronym = $nameParts[1];
                    }
                    else
                    {
                        $acronym = $nameParts[0];
                    }
                }
                $metaData = array(
                    'filename' => $asset->getFullName(),
                    'width' => $size->getWidth(),
                    'height' => $size->getHeight(),
                    'copyright' => $copyrightProvider->resolveAcronym($acronym)
                );
                $assetService->update($asset, $metaData);
                $assetIds[] = $asset->getIdentifier();
            }
            catch(Exception $e)
            {
                echo("[".__METHOD__."] Error while procssing attachment, " . $e->getMessage());
                continue;
            }
        }
        return $assetIds;
    }
}
