<?php

/**
 * The EventXPlacesXmlParser class is an abstract implementation of the IXmlParser interface.
 * We are not extending the BaseXmlParser class here, because we will want to refactor that one first,
 * before baking in the old interface all over the place.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 * @subpackage      Import/EventX
 */
class EventXPlacesXmlParser implements IXmlParser
{
    const DEFAULT_CATEGORY = 'empty';

    const TIP_NAMESPACE_PREFIX = 'tip';

    const TBO_NAMESPACE_PREFIX = 'tbo';

    // ---------------------------------- <BaseXmlParser IMPL> -----------------------------------

    public function parseXml($xmlSource)
    {
        $document = new DOMDocument('1.0', 'utf-8');
        if (is_file($xmlSource))
        {
            $document->load($xmlSource);
        }
        else
        {
            $document->loadXML($xmlSource);
        }
        return $this->processDocument($document);
    }

    protected function processDocument(DOMDocument $document)
    {
        $xpath = new DOMXPath($document);
        $tipNsUri = $document->lookupNamespaceUri(self::TIP_NAMESPACE_PREFIX);
        $tboNsUri = $document->lookupNamespaceUri(self::TBO_NAMESPACE_PREFIX);
        $xpath->registerNamespace(self::TIP_NAMESPACE_PREFIX, $tipNsUri);
        $xpath->registerNamespace(self::TBO_NAMESPACE_PREFIX, $tboNsUri);

        $data = array();

        foreach ($xpath->query('//tbo:veranstaltungsort') as $eventLocationElement)
        {
            $parentLocation = $eventLocationElement->getElementsByTagNameNs($tboNsUri, 'masteradresse')->item(0);
            if ($parentLocation)
            {
                // only import master locations, sublocations are found by search master location's 'sublocations' attribute
                continue;
            }

            // adress data
            $adressElement = $eventLocationElement->getElementsByTagNameNs($tboNsUri, 'adresse')->item(0);
            $address = array();
            if ($adressElement)
            {
                $streetNode = $adressElement->getElementsByTagNameNs($tipNsUri, 'strasse')->item(0);
                $houseNoNode = $adressElement->getElementsByTagNameNs($tipNsUri, 'hausnummer')->item(0);
                $zipCodeNode = $adressElement->getElementsByTagNameNs($tipNsUri, 'plz')->item(0);
                $cityNode = $adressElement->getElementsByTagNameNs($tipNsUri, 'ort')->item(0);
                $districtNode = $eventLocationElement->getElementsByTagNameNs($tboNsUri, 'stadtteil')->item(0);
                $adress = array(
                    'street' => $streetNode ? $streetNode->nodeValue : NULL,
                    'houseNumber' => $houseNoNode ? $houseNoNode->nodeValue : NULL,
                    'uzip' => $zipCodeNode ? $zipCodeNode->nodeValue : NULL,
                    'city' => $cityNode ? $cityNode->nodeValue : NULL,
                    'district' => $districtNode ? $districtNode->nodeValue : NULL
                );
            }

            // telefon/telefax numbers
            $telefon = NULL;
            if (($telefonNode = $eventLocationElement->getElementsByTagNameNs($tboNsUri, 'telefon1')->item(0)))
            {
                if (
                    ($prefixNode = $telefonNode->getElementsByTagNameNs($tipNsUri, 'ortsvorwahl')->item(0)) &&
                    ($numberNode = $telefonNode->getElementsByTagNameNs($tipNsUri, 'durchwahl')->item(0))
                )
                {
                    $telefon = sprintf('%s/%s', $prefixNode->nodeValue, $numberNode->nodeValue);
                }
            }
            $fax = NULL;
            if (($faxNode = $eventLocationElement->getElementsByTagNameNs($tboNsUri, 'telefax')->item(0)))
            {
                if (
                    ($prefixNode = $faxNode->getElementsByTagNameNs($tipNsUri, 'ortsvorwahl')->item(0)) &&
                    ($numberNode = $faxNode->getElementsByTagNameNs($tipNsUri, 'durchwahl')->item(0))
                )
                {
                    $fax = sprintf('%s/%s', $prefixNode->nodeValue, $numberNode->nodeValue);
                }
            }

            // category data & keywords
            $categoryNode = $eventLocationElement->getElementsByTagNameNs($tboNsUri, 'rubrik')->item(0);
            $nameNode = $eventLocationElement->getElementsByTagNameNs($tipNsUri, 'name')->item(0);
            $midasKeywords = array();
            if ($categoryNode)
            {
                foreach (explode('/', $categoryNode->nodeValue) as $eventXCategory)
                {
                    $eventXCategory = trim($eventXCategory);
                    if (! empty($eventXCategory))
                    {
                        $midasKeywords[] = $eventXCategory;
                    }
                }
            }

            // opening times
            $openingTimes = array();
            if (($openingTimesNode = $eventLocationElement->getElementsByTagNameNs($tboNsUri, 'oeffnungszeiten')->item(0)))
            {
                foreach ($openingTimesNode->getElementsByTagNameNs($tipNsUri, 'wochentagzeiten') as $timeNode)
                {
                    if (
                    ($fromNode = $timeNode->getElementsByTagNameNs($tipNsUri, 'von')->item(0)) &&
                    ($toNode = $timeNode->getElementsByTagNameNs($tipNsUri, 'bis')->item(0))
                    )
                    {
                        $openingTimes[] = array(
                            'from' => array(
                                'day' => $timeNode->getAttribute('wochentag'),
                                'time' => $fromNode->nodeValue
                            ),
                            'to' => array(
                                'day' => $timeNode->getAttribute('wochentag'),
                                'time' => $toNode->nodeValue
                            )
                        );
                    }
                }
            }

            // public transport
            $publicTransports = array();
            if (($pubTransportNode = $eventLocationElement->getElementsByTagNameNs($tboNsUri, 'verkehrsanbindung')->item(0)))
            {
                foreach ($pubTransportNode->getElementsByTagNameNs($tipNsUri, 'verkehrsanschluss') as $transportNode)
                {
                    $publicTransports[] = array(
                        'type' => $transportNode->getAttribute('art'),
                        'value' => $transportNode->nodeValue
                    );
                }
            }

            // misc data (mail, website ...)
            $filemakerNode = $eventLocationElement->getElementsByTagNameNs($tboNsUri, 'filemaker')->item(0);
            $websiteNode = $eventLocationElement->getElementsByTagNameNs($tboNsUri, 'url')->item(0);
            $emailNode = $eventLocationElement->getElementsByTagNameNs($tboNsUri, 'email')->item(0);

            $filemakerId = NULL;
            if (($fileMakerNode = $eventLocationElement->getElementsByTagNameNs($tboNsUri, 'filemakerId')->item(0)))
            {
                $filemakerId = $fileMakerNode->nodeValue;
            }

            // pluck the result together from what we've gathered
            $data[] = array(
                'identifier' => $eventLocationElement->getAttribute('key'),
                'name' => $nameNode ? $nameNode->nodeValue : NULL,
                'address' => $adress,
                'telefon' => $telefon,
                'fax' => $fax,
                'website' => $websiteNode ? $websiteNode->nodeValue : NULL,
                'email' => $emailNode ? $emailNode->nodeValue : NULL,
                'filemaker_id' => $filemakerId,
                'keywords' => $midasKeywords,
                'category' => $categoryNode ? $categoryNode->nodeValue : self::DEFAULT_CATEGORY,
                'public_transport' => $publicTransports,
                'opening_times' => $openingTimes
            );
        }

        return $data;
    }

    // ---------------------------------- </BaseXmlParser IMPL> ----------------------------------
}
