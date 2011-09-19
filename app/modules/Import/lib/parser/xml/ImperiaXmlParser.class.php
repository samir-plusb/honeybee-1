<?php

/**
 * The ImperiaXmlParser class is an abstract implementation of the BaseXmlParser base class.
 * It provides parsing of the imperia export xml format.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Parser
 */
class ImperiaXmlParser extends BaseXmlParser
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds an array with key to xpath mappings,
     * containing xpath expressions that return results,
     * whcih can directoy exposed without further processing.
     *
     * @var     array
     */
    protected static $simpleMappings = array(
        'title'     => '/imperia/body/article/title',
        'subtitle'  => '/imperia/body/article/subtitle',
        'kicker'    => '/imperia/body/article/kicker',
        'directory' => '/imperia/head/directory',
        'filename'  => '/imperia/head/filename'
    );
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <IXmlParser IMPL> --------------------------------------
    
    /**
     * Parse the given the $xmlString thereby expecting the imperia article
     * export xml format.
     * 
     * @param       type $xmlString
     * 
     * @return      array
     */
    public function parseXml($xmlString)
    {
        $xPath = new DOMXPath(
            $this->createDom($xmlString)
        );
        
        $data = $this->extractSimpleValues($xPath);
        $data['modified'] = $this->fetchNodeValueAsDate($xPath, '/imperia/head/modified');
        $data['publish'] = $this->fetchNodeValueAsDate($xPath, '/imperia/head/publish');
        $data['expiry'] = $this->fetchNodeValueAsDate($xPath, '/imperia/head/expiry');
        $data['paragraphs'] = BaseXmlParser::nodeListToArray(
            $xPath->query('/imperia/body/article//paragraph/text')
        );
        $data['categories'] = BaseXmlParser::nodeListToArray(
             $xPath->query('/imperia/head/categories/category')
        );
        $data['keywords'] = explode(
            '-',
            $this->fetchFirstNodeValue($xPath, '/imperia/head/meta[@name="keywords"]/@content')
        );
        $data['images'] = array();
        $imageList = $xPath->query('/imperia/body/article//image');
        
        foreach ($imageList as $imageNode)
        {
            if (($imageInfo = $this->imageNodeToArray($imageNode)))
            {
                $data['images'][] = $imageInfo;
            }
        }
        
        return $data;
    }
    
    // ---------------------------------- </IXmlParser IMPL> -------------------------------------
    
    
    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Try to create a new DOMDocument from the given xml string.
     *
     * @param       string $xmlString
     *
     * @return      DOMDocument
     *
     * @throws      DataRecordException If the xml can't be parsed.
     */
    protected function createDom($xmlString)
    {
        libxml_clear_errors();
        $domDoc = new DOMDocument();
        
        // @codingStandardsIgnoreStart
        if (!@$domDoc->loadXML($xmlString)) // @codingStandardsIgnoreEnd
        {
            $errors = libxml_get_errors();
            $msg = array();

            foreach ($errors as $error)
            {
                $msg[] = sprintf('%d (%d,%d) %s', $error->code, $error->line, $error->column, $error->message);
            }

            throw new DataRecordException('Xml parse errors: '.join(', ', $msg));
        }

        return $domDoc;
    }
    
    /**
     * Run all query expressions provided from our $simpleMappings
     * array on a given xpath and return an array containing the first node value
     * of each resulting node list.
     * 
     * @param       DOMXpath $xPath
     * 
     * @return      mixed
     */
    protected function extractSimpleValues(DOMXpath $xPath)
    {
        $data = array();
        
        foreach (self::$simpleMappings as $name => $xPathExpr)
        {
            $data[$name] = $this->fetchFirstNodeValue($xPath, $xPathExpr);
        }
        
        return $data;
    }
    
    /**
     * Query the given xpath for the passed expression
     * and return the value of the first node from the query result list.
     * 
     * @param       DOMXpath $xPath
     * @param       string $xPathExpr
     * 
     * @return      mixed
     */
    protected function fetchFirstNodeValue(DOMXpath $xPath, $xPathExpr)
    {
        $nodeList = $xPath->query($xPathExpr);
        
        if (0 < $nodeList->length)
        {
            return trim($nodeList->item(0)->nodeValue);
        }
        
        return NULL;
    }
    
    /**
     * Query the given xpath for the passed xpression
     * and throw the result into a new DateTime instance thereby returning the latter.
     * Returns NULL if no value is found for the given expression.
     * 
     * @param       DOMXpath $xPath
     * @param       string $xPathExpr
     * 
     * @return      DateTime 
     */
    protected function fetchNodeValueAsDate(DOMXpath $xPath, $xPathExpr)
    {
        $nodeValue = $this->fetchFirstNodeValue($xPath, $xPathExpr);
        
        if ($nodeValue)
        {
            return new DateTime($nodeValue);
        }
        
        return NULL;
    }
    
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