<?php

/**
 * The XmlBasedDataRecord class is an abstract implementation of the ImportBaseDataRecord base class.
 * It provides handling xml based data and separates the process into the steps of fetching node data
 * by evaluating a number of xpath and expressions and then normalizing the retained data into a common structure.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
abstract class XmlBasedDataRecord extends ImportBaseDataRecord
{
    /**
     * Holds our domDocument during parseData runtime.
     *
     * @var         DOMDocument
     */
    private $domDocument;

    // ---------------------------------- <ABSTRACT METHODS> -------------------------------------

    /**
     * Return an array holding fieldnames and corresponding xpath queries
     * that will be evaluated and mapped to the correlating field.
     *
     * @return      array
     */
    abstract protected function getFieldMap();


    /**
     * Normalize the given xpath results.
     *
     * @param       array $data Contains result from processing our field map.
     *
     * @return      array
     */
    abstract protected function normalizeData(array $data);

    // ---------------------------------- </ABSTRACT METHODS> ------------------------------------


    // ---------------------------------- <ImportBaseDataRecord IMPL> ----------------------------

    /**
     * Parse the given xml data and return a normalized array.
     *
     * @param       mixed $data
     *
     * @return      array
     *
     * @see         ImportBaseDataRecord::parseData()
     *
     * @uses        XmlBasedDataRecord::createDom()
     * @uses        XmlBasedDataRecord::evaluateDocument()
     */
    protected function parseData($data)
    {
        $this->setUp($data);

        $parsedData = $this->evaluateDocument();

        $this->tearDown();


        return $parsedData;
    }

    // ---------------------------------- </ImportBaseDataRecord IMPL> ---------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    /**
     * Set everything up for parsing our data.
     * 
     * @param       string $data 
     */
    protected function setUp($data)
    {
        $this->domDocument = $this->createDom($data);
    }

    /**
     * Cleanup up after parsing data.
     */
    protected function tearDown()
    {
        unset($this->domDocument);
        $this->domDocument = NULL;
    }
    
    /**
     * Return the DOMDocument that we are currently parsing.
     * May only be invoked between setUp() and tearDown().
     * 
     * @return      DOMDocument
     */
    protected function getDocument()
    {
        if (NULL === $this->domDocument)
        {
            throw new DataRecordException(
                "Calling the " . __METHOD__ . " method outside of the parse methods execution scope is not supported."
            );
        }

        return $this->domDocument;
    }

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

        if (!@$domDoc->loadXML($xmlString))
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
     * Evaluate the given DOMDocument,
     * thereby obtaining our desired data
     * and return a normalized array representation of latter.
     *
     * @param       DOMDocument $domDoc
     *
     * @return      array
     *
     * @uses        XmlBasedDataRecord::collectData()
     * @uses        XmlBasedDataRecord::normalizeData()
     */
    protected function evaluateDocument()
    {
        $rawData = $this->collectData(
            $this->getDocument()
        );

        return $this->normalizeData($rawData);
    }

    /**
     * Executes the xpath expressions provided by our getFieldMap method
     * and returns an array with each fieldname as key for the corresponding xpath query result.
     *
     * @param       DOMDocument $domDoc
     *
     * @return      array
     *
     * @uses        XmlBasedDataRecord::getFieldMap()
     */
    protected function collectData(DOMDocument $domDoc)
    {
        $xPath = new DOMXPath($domDoc);
        $data = array();

        foreach ($this->getFieldMap() as $dataKey => $dataExpr)
        {
            $nodeList = $xPath->query($dataExpr);
            $data[$dataKey] = (0 < $nodeList->length) ? $nodeList : FALSE;
        }

        return $data;
    }

    /**
     * Acts like join() for the nodeValues of the node-list.
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
     * Translate a node list to a simple string array.
     *
     * @param       DOMNodeList $nodeList
     */
    protected function nodeListToArray(DOMNodeList $nodeList)
    {
        $content = array();

        for ($i = 0; $i < $nodeList->length; $i++)
        {
            $value = trim($nodeList->item($i)->nodeValue);

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

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>