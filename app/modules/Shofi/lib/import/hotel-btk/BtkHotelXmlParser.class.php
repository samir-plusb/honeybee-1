<?php

/**
 * The HotelXmlParser class is an abstract implementation of the IXmlParser interface.
 * We are not extending the BaseXmlParser class here, because we will want to refactor that one first,
 * before baking in the old interface all over the place.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 * @subpackage      Import/Hotel
 */
class BtkHotelXmlParser implements IXmlParser
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
        foreach ($xpath->query('//serviceProvider') as $providerNode)
        {
            $data[] = $this->transformProviderNode($providerNode);
        }
        return $data;
    }

    protected function transformProviderNode(DOMElement $providerNode)
    {
        $descriptionNode = $providerNode->getElementsByTagName('description')->item(0);
        $bookingInfoNode = $providerNode->getElementsByTagName('booking-info')->item(0);
        $checkInfoNode = $providerNode->getElementsByTagName('check-in-out')->item(0);
        $extrasNode = $providerNode->getElementsByTagName('conditionsextras')->item(0);
        
        $values = array(
            'identifier' => $providerNode->getAttribute('code'),
            'name' => $providerNode->getElementsByTagName('name')->item(0)->nodeValue,
            'type' => $providerNode->getElementsByTagName('type')->item(0)->nodeValue,
            'address' => $this->extractAdress($providerNode),
            'images' => $this->extractImages($providerNode),
            'features' => $this->extractFeatures($providerNode),
            'description' => $descriptionNode->getElementsByTagName('de')->item(0)->nodeValue,
            'booking-info' => $bookingInfoNode->getElementsByTagName('de')->item(0)->nodeValue,
            'check-in-out' => $checkInfoNode->getElementsByTagName('de')->item(0)->nodeValue,
            'conditionsextras' => $extrasNode->getElementsByTagName('de')->item(0)->nodeValue
        );

        return array_filter($values, function($value) {
            return ! empty($value);
        });
    }

    protected function extractAdress(DOMElement $providerNode)
    {
        $addressNode = $providerNode->getElementsByTagName('address')->item(0);
        $coordsNode = $providerNode->getElementsByTagName('coordinates')->item(0);

        return array(
            'district' => $providerNode->getElementsByTagName('district')->item(0)->nodeValue,
            'street' => $addressNode->getElementsByTagName('street')->item(0)->nodeValue,
            'company' => $addressNode->getElementsByTagName('company')->item(0)->nodeValue,
            'city' => $addressNode->getElementsByTagName('city')->item(0)->nodeValue,
            'uzip' => $addressNode->getElementsByTagName('zipCode')->item(0)->nodeValue,
            'coords' => array(
                'lon' => $coordsNode->getElementsByTagName('longitude')->item(0)->nodeValue,
                'lat' => $coordsNode->getElementsByTagName('latitude')->item(0)->nodeValue
            )
        );
    }

    protected function extractFeatures(DOMElement $providerNode)
    {
        $features = array();
        $featuresNode = $providerNode->getElementsByTagName('features')->item(0);
        foreach ($featuresNode->getElementsByTagName('group') as $groupNode)
        {
            $featureNameNode = $groupNode->getElementsByTagName('name')->item(0);
            $name = $featureNameNode->getElementsByTagName('de')->item(0)->nodeValue;
            $currentFeatureList = array();
            foreach ($groupNode->getElementsByTagName('feature') as $featureNode)
            {
                $labelNode = $featureNode->getElementsByTagName('name')->item(0);
                $valueNode = $featureNode->getElementsByTagName('number')->item(0);
                $label = trim($labelNode->getElementsByTagName('de')->item(0)->nodeValue);
                $value = $valueNode ? trim($valueNode->nodeValue) : NULL;
                if (! empty($label))
                {
                    $floatVal = (float)$value;
                    $currentFeatureList[] = $label . (! empty($floatVal) ? ': '.$value : '');
                }
            }
            $features[$name] = $currentFeatureList;
        }
        if (($starsNode = $providerNode->getElementsByTagName('stars')->item(0)))
        {
            $value = trim($starsNode->nodeValue);
            if (! empty($value))
            {
                $features['stars'] = array($starsNode->nodeValue);
            }
        }
        if (($starsPlusNode = $providerNode->getElementsByTagName('stars-plus')->item(0)))
        {
            $features['stars-plus'] = array($starsPlusNode->nodeValue);
        }
        
        return $features;
    }

    protected function extractImages(DOMElement $providerNode)
    {
        $images = array();
        $imagesNode = $providerNode->getElementsByTagName('images')->item(0);
        foreach ($imagesNode->getElementsByTagName('image') as $imageNode) 
        {
            $titleNode = $imageNode->getElementsByTagName('title')->item(0)->getElementsByTagName('de')->item(0);
            $altNode = $imageNode->getElementsByTagName('alt')->item(0)->getElementsByTagName('de')->item(0);
            $images[] = array(
                'uri' => $imageNode->getElementsByTagName('uri')->item(0)->nodeValue,
                'thumbnail' => $imageNode->getElementsByTagName('thumbnail')->item(0)->nodeValue,
                'width' => $imageNode->getElementsByTagName('width')->item(0)->nodeValue,
                'height' => $imageNode->getElementsByTagName('height')->item(0)->nodeValue,
                'title' => $titleNode ? $titleNode->nodeValue : '',
                'alt' => $altNode ? $altNode->nodeValue : ''
            );
            
        }
        return $images;
    }

    // ---------------------------------- </BaseXmlParser IMPL> ----------------------------------
}

?>
