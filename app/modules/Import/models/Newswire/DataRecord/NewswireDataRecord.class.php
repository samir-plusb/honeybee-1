<?php

/**
 * Base handling for newswire agency messages
 *
 * @version         $ID:$
 * @author          Tom Anheyer
 * @package         Import
 * @subpackage      Newswire
 */
abstract class NewswireDataRecord extends XmlBasedDataRecord
{
    /**
     * Get DOMNode as XML string
     *
     * @param       DOMNode $node XML-Node to output
     * 
     * @return      string XML string
     */
    protected function nodeToString(DOMNode $node)
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

    /**
     * acts like join() for the nodeValues of the node-list
     *
     * @param       DOMNodeList $nodeList
     * @param       string $separator
     * 
     * @return      string
     */
    protected function joinNodeList(DOMNodeList $nodeList, $separator)
    {
        $content = $this->nodeListToArray($nodeList);
        
        return join($separator, $content);
    }

    /**
     * translate a node list to a simple string array
     *
     * @param       DOMNodeList $nodeList
     */
    protected function nodeListToArray(DOMNodeList $nodeList)
    {
        $content = array();
        
        for ($i = 0; $i < $nodeList->length; $i++) {
            $content[] = $nodeList->item($i)->nodeValue;
        }
        
        return $content;
    }
}

?>