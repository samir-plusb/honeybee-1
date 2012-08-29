<?php

/**
 * The TheaterXmlParser class is an abstract implementation of the IXmlParser interface.
 * We are not extending the BaseXmlParser class here, because we will want to refactor that one first,
 * before baking in the old interface all over the place.
 *
 * @version         $Id: ImperiaXmlParser.class.php 1299 2012-06-12 16:09:14Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 * @subpackage      Import/Theater
 */
class TelavsionTheaterXmlParser implements IXmlParser
{
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
        $data = array();
        foreach ($xpath->query('//KINO') as $theaterNode)
        {
            $data[] = $this->transformTheaterNode($theaterNode);
        }
        return $data;
    }

    protected function transformTheaterNode(DOMElement $theaterNode)
    {
        $adressNode = $theaterNode->getElementsByTagName('ADRESSE')->item(0);
        $screensNode = $theaterNode->getElementsByTagName('SAELE')->item(0);

        $data = array(
            'id' => $theaterNode->getAttribute('ID'),
            'name' => $theaterNode->getElementsByTagName('NAME')->item(0)->nodeValue,
            'telefon' => $theaterNode->getElementsByTagName('TELEFON')->item(0)->nodeValue,
            'url' => $theaterNode->getElementsByTagName('URL')->item(0)->nodeValue,
            'prices' => $theaterNode->getElementsByTagName('PREISE')->item(0)->nodeValue,
            'description' => $theaterNode->getElementsByTagName('BESCHREIBUNG')->item(0)->nodeValue,
            'location' => array(
                'district' => $adressNode->getElementsByTagName('STADTTEIL')->item(0)->nodeValue,
                'postalCode' => $adressNode->getElementsByTagName('PLZ')->item(0)->nodeValue,
                'city' => $adressNode->getElementsByTagName('ORT')->item(0)->nodeValue,
                'street' => $adressNode->getElementsByTagName('STRASSE')->item(0)->nodeValue
            )
        );

        $screens = array();
        foreach ($screensNode->getElementsByTagName('SAAL') as $screenNode)
        {
            $screens[] = array(
                'id' => $screenNode->getAttribute('ID'),
                'name' => $screenNode->getAttribute('NAME'),
                'seats' => $screenNode->getAttribute('SITZPLAETZE')
            );
        }
        $data['screens'] = $screens;

        return $data;
    }

    // ---------------------------------- </BaseXmlParser IMPL> ----------------------------------
}

?>
