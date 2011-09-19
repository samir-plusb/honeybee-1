<?php

/**
 * The IXmlParser interface defines the public api for all xml parsers inside of our project.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Parser
 */
interface IXmlParser
{
    /**
     * Parse the given xml and return assoc array reflecting the xml structure in  a desired way.
     * 
     * @param       string $xmlString
     * 
     * @return      array
     */
    public function parseXml($xmlString);
}

?>