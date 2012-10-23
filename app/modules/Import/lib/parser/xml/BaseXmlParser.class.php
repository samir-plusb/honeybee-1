<?php

/**
 * The BaseXmlParser class is an abstract implementation of the IXmlParser interface.
 * It provides some basic functionality that is common to all xml parsers.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Parser/Xml
 */
abstract class BaseXmlParser implements IXmlParser
{
    // ---------------------------------- <ABSTRACT METHODS> -------------------------------------

    abstract protected function process(DOMXpath $xpath);

    // ---------------------------------- </ABSTRACT METHODS> ------------------------------------


    // ---------------------------------- <IXmlParser IMPL> --------------------------------------

    public function parseXml($xmlString)
    {
        $document = self::createDom($xmlString);
        $xpath = new DOMXPath($document);
        return $this->process($xpath);
    }

    // ---------------------------------- </IXmlParser IMPL> -------------------------------------


    // ---------------------------------- <STATIC TOOLKIT METHODS> -------------------------------

    /**
     * Try to create a new DOMDocument from the given xml string.
     *
     * @param       string $xmlString
     *
     * @return      DOMDocument
     *
     * @throws      XmlParserException If the xml can't be parsed.
     */
    public static function createDom($xmlString)
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
            throw new XmlParserException('Xml parse errors: '.join(', ', $msg));
        }
        return $domDoc;
    }

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

    // ---------------------------------- </STATIC TOOLKIT METHODS> ------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Run all query expressions provided from our $simpleMappings
     * array on a given xpath and return an array containing the first node value
     * of each resulting node list.
     *
     * @param       DOMXpath $xpath
     *
     * @return      mixed
     */
    protected function evaluateXpaths(DOMXpath $xpath, array $expressions)
    {
        $data = array();
        foreach ($expressions as $key => $expression)
        {
            $data[$key] = $this->evaluateXpath($xpath, $expression);
        }
        return $data;
    }

    protected function evaluateXpath(DOMXPath $xpath, $expression)
    {
        $nodeList = $xpath->query($expression);
        $value = NULL;
        if (0 === $nodeList->length)
        {
            return $value;
        }
        if (1 === $nodeList->length)
        {
            $value = trim($nodeList->item(0)->nodeValue);
        }
        else
        {
            $value = self::nodeListToArray($nodeList);
        }
        return $value;
    }

    protected function parseStringAsDate($dateString)
    {
        if (empty($dateString) || ! is_string($dateString))
        {
            return NULL;
        }
        if (preg_match('/^\d{8}T\d{6}[+-]\d{4}$/', $dateString))
        {
            return new DateTime($dateString);
        }
        else if (preg_match('/^(\d{8}T\d{6})Z$/', $dateString, $m))
        {
            return new DateTime($m[1].'+0000');
        }
        return new DateTime($dateString);
    }

    /**
     * Query the given xpath for the passed xpression
     * and throw the result into a new DateTime instance thereby returning the latter.
     * Returns NULL if no value is found for the given expression.
     *
     * @param       DOMXpath $xpath
     * @param       string $expression
     *
     * @return      DateTime
     */
    protected function fetchNodeValueAsDate(DOMXpath $xpath, $expression)
    {
        return $this->parseStringAsDate(
            $this->fetchFirstNodeValue($xpath, $expression)
        );
    }

    protected function fetchFirstNodeValue(DOMXPath $xpath, $expression)
    {
        $value = $this->evaluateXpath($xpath, $expression);
        if (is_array($value) && 0 < count($value))
        {
            return $value[0];
        }
        return $value;
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>
