<?php

/**
 * The ImperiaXmlParser class is an abstract implementation of the BaseXmlParser base class.
 * It provides parsing of the imperia export xml format.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Import/Imperia
 */
class ImperiaXmlParser extends BaseXmlParser
{
    // ---------------------------------- <BaseXmlParser IMPL> -----------------------------------

    protected function process(DOMXpath $xpath)
    {
        $xpathExpressions = array(
            'title'     => '/imperia/body/article/title',
            'subtitle'  => '/imperia/body/article/subtitle',
            'kicker'    => '/imperia/body/article/kicker',
            'directory' => '/imperia/head/directory',
            'filename'  => '/imperia/head/filename'
        );

        $data = array_merge(
            $this->evaluateXpaths($xpath, $xpathExpressions),
            array(
                'modified' => $this->fetchNodeValueAsDate($xpath, '/imperia/head/modified'),
                'publish' => $this->fetchNodeValueAsDate($xpath, '/imperia/head/publish'),
                'expiry' => $this->fetchNodeValueAsDate($xpath, '/imperia/head/expiry'),
                'paragraphs' => self::nodeListToArray(
                    $xpath->query('/imperia/body/article//paragraph/text')
                ),
                'categories' => self::nodeListToArray(
                    $xpath->query('/imperia/head/categories/category')
                ),
                'keywords' => explode(
                    '-',
                    $this->fetchFirstNodeValue($xpath, '/imperia/head/meta[@name="keywords"]/@content')
                ),
                'images' => array()
            )
        );

        $imageList = $xpath->query('/imperia/body/article//image');
        foreach ($imageList as $imageNode)
        {
            if (($imageInfo = $this->imageNodeToArray($imageNode)))
            {
                $data['images'][] = $imageInfo;
            }
        }
        return $data;
    }

    // ---------------------------------- </BaseXmlParser IMPL> ----------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Convert the image node to an assoc array.
     *
     * @param       DOMNode $imageNode
     *
     * @return      array
     */
    protected function imageNodeToArray(DOMNode $imageNode)
    {
        $assetDataNodes = array('src', 'caption', 'alt');
        $assetData = array();
        foreach ($imageNode->childNodes as $childNode)
        {
            if (in_array($childNode->nodeName, $assetDataNodes))
            {
                $assetData[$childNode->nodeName] = trim($childNode->nodeValue);
            }
        }
        if (isset($assetData['src']))
        {
            return $assetData;
        }
        return NULL;
    }

    // ---------------------------------- <WORKING METHODS> --------------------------------------
}

?>