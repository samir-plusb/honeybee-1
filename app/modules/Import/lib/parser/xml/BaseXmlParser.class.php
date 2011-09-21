<?php

/**
 * The BaseXmlParser class is an abstract implementation of the IXmlParser interface.
 * It provides some basic functionality that is common to all xml parsers.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Parser
 */
abstract class BaseXmlParser implements IXmlParser
{
    /**
     * Acts like join() for the nodeValues of the node-list.
     *
     * @param       DOMNodeList $nodeList
     * @param       string $separator
     *
     * @return      string
     */
    public static function joinNodeList(DOMNodeList $nodeList, $separator)
    {
        $content = self::nodeListToArray($nodeList);

        return join($separator, $content);
    }

    /**
     * Translate a node list to a simple string array.
     *
     * @param       DOMNodeList $nodeList
     */
    public static function nodeListToArray(DOMNodeList $nodeList)
    {
        $content = array();

        foreach ($nodeList as $node)
        {
            $value = trim($node->nodeValue);

            if ($value)
            {
                $content[] = $value;
            }
        }

        return $content;
    }

    /**
     * Get DOMNode as XML string.
     *
     * @param       DOMNode $node XML-Node to output
     *
     * @return      string XML string
     */
    public static function nodeToString(DOMNode $node)
    {
        if ($node instanceof DOMDocument)
        {
            $output = $node->saveXML();
        }
        else
        {
            $doc = new DOMDocument;
            $domNode = $doc->importNode($node, TRUE);
            $doc->appendChild($domNode);
            $output = $doc->saveXML($domNode, LIBXML_NOXMLDECL);
        }

        return $output;
    }
}

?>