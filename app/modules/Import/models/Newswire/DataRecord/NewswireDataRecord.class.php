<?php
/**
 * Base handling for newswire agency messages
 *
 * @package Import
 * @subpackage Newswire
 * @version $ID:$
 * @author Tom Anheyer
 *
 */
abstract class NewswireDataRecord extends XmlBasedDataRecord
{

    /**
     * Get DOMNode as XML string
     *
     * @param DOMNode $node XML-Node to output
     * @return string XML string
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
            $domNode = $doc->importNode($node, true);
            $doc->appendChild($domNode);
            $output = $doc->saveXML($domNode, LIBXML_NOXMLDECL);
        }
        return $output;
    }

    /**
     * acts like join() for the nodeValues of the node-list
     *
     * @param DOMNodeList $nl
     * @param string $separator
     * @return string
     */
    protected function joinNodeList(DOMNodeList $nl, $separator)
    {
        $content = $this->nodeListToArray($nl);
        return join($separator, $content);
    }


    /**
     * translate a node list to a simple string array
     *
     * @param DOMNodeList $nl
     */
    protected function nodeListToArray(DOMNodeList $nl)
    {
        $content = array();
        for ($i = 0; $i < $nl->length; $i++) {
            $content[] = $nl->item($i)->nodeValue;
        }
        return $content;
    }

}

