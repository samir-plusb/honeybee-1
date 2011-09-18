<?php

/**
 * The DpaNitfNewswireDataRecord class is a concrete implementation of the NitfNewswireDataRecord base class.
 * It provides processing data in the nitf format for data coming from the dpa.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <tom.anheyer@berlinonline.de>
 * @package         Import
 * @subpackage      Newsire
 */
class DpaNitfNewswireDataRecord extends NitfNewswireDataRecord
{
    // ---------------------------------- <XmlBasedDataRecord IMPL> ------------------------------
    
    /**
     * Return a list of field keys to corresponding xpath expressions.
     *
     * @see         collectData()
     * @see         XmlBasedDataRecord::getFieldMap()
     *
     * @return      array
     */
    protected function getFieldMap()
    {
        return array_merge(
            parent::getFieldMap(), 
            array(
                self::PROP_SUBTITLE  => '//byline',
                self::PROP_COPYRIGHT => '//meta[@name="copyright"]/@content',
                self::PROP_SOURCE    => '//meta[@name="origin"]/@content'
            )
        );
    }
    
    /**
     * Filter the collected data.
     *
     * Maps existing xml node lists to strings or array of strings.
     *
     * @see         XmlBasedDataRecord::normalizeData()
     *
     * @return      array
     */
    protected function normalizeData(array $data)
    {
        $normalized = parent::normalizeData($data);
        $normalized['links'] = $this->importLinks();

        if (isset($data[self::PROP_KEYWORDS]) && $data[self::PROP_KEYWORDS])
        {
            $list = array();

            foreach ($data[self::PROP_KEYWORDS] as $keyword)
            {
                $list = array_merge($list, explode('/', $keyword->nodeValue));
            }

            $normalized[self::PROP_KEYWORDS] = array_filter($list);
        }

        return $normalized;
    }
    
    // ---------------------------------- </XmlBasedDataRecord IMPL> -----------------------------
    
    
    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    /**
     * Import nitf tables.
     *
     * @param       DOMDocument $domDoc
     *
     * @return      array of xml tagged strings
     */
    protected function importLinks()
    {
        $data = array();
        $domDoc = $this->getDocument();
        $xpath = new DOMXPath($domDoc);

        foreach ($xpath->query('//body.content/block[@style="EXTERNAL-LINKS"]/p/a') as $node)
        {
            $data[] = $this->nodeToString($node);
        }

        return $data;
    }
    
    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>