<?php

/**
 * The DpaNitfNewswireXmlParser class is a concrete implementation of the BaseXmlParser base class.
 * It provides parsing of the dpa(neswire)-nitf xml format.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Import/Newswire
 */
class DpaNitfNewswireXmlParser extends NitfNewswireXmlParser
{
    // ---------------------------------- <BaseXmlParser IMPL> -----------------------------------

    protected function process(DOMXpath $xpath)
    {
        $xpathExpressions = array(
            'headline'  => '//byline',
            'copyright' => '//meta[@name="copyright"]/@content',
            'source'    => '//meta[@name="origin"]/@content'
        );
        return array_merge(
            parent::process($xpath),
            $this->evaluateXpaths($xpath, $xpathExpressions),
            array(
                'links' => $this->importLinks($xpath)
            )
        );
    }

    // ---------------------------------- </BaseXmlParser IMPL> ----------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    protected function importLinks(DOMXPath $xpath)
    {
        $data = array();
        foreach ($xpath->query('//body.content/block[@style="EXTERNAL-LINKS"]/p/a') as $node)
        {
            $data[] = BaseXmlParser::nodeToString($node);
        }
        return $data;
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>
