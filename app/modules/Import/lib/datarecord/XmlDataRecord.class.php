<?php

/**
 * The XmlDataRecord class is an abstract implementation of the BaseDataRecord base class.
 * It provides handling xml based data and separates the process into the steps of fetching node data
 * by evaluating a number of xpath and expressions and then normalizing the retained data into a common structure.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      DataRecord
 */
abstract class XmlDataRecord extends BaseDataRecord
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


    // ---------------------------------- <BaseDataRecord IMPL> ----------------------------

    /**
     * Parse the given xml data and return a normalized array.
     *
     * @param       mixed $data
     *
     * @return      array
     *
     * @see         BaseDataRecord::parseData()
     *
     * @uses        XmlDataRecord::createDom()
     * @uses        XmlDataRecord::evaluateDocument()
     */
    protected function parseData($data)
    {
        $this->setUp($data);

        $parsedData = $this->evaluateDocument();

        $this->tearDown();


        return $parsedData;
    }

    // ---------------------------------- </BaseDataRecord IMPL> ---------------------------


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
     * Evaluate the given DOMDocument,
     * thereby obtaining our desired data
     * and return a normalized array representation of latter.
     *
     * @param       DOMDocument $domDoc
     *
     * @return      array
     *
     * @uses        XmlDataRecord::collectData()
     * @uses        XmlDataRecord::normalizeData()
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
     * @uses        XmlDataRecord::getFieldMap()
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
     *
     * @deprecated  Use BaseXmlParser::joinNodeList()
     */
    protected function joinNodeList(DOMNodeList $nodeList, $separator)
    {
        return BaseXmlParser::joinNodeList($nodeList, $separator);
    }

    /**
     * Translate a node list to a simple string array.
     *
     * @param       DOMNodeList $nodeList
     *
     * @deprecated  Use BaseXmlParser::nodeListToArray()
     */
    protected function nodeListToArray(DOMNodeList $nodeList)
    {
        return BaseXmlParser::nodeListToArray($nodeList);
    }

    /**
     * Get DOMNode as XML string.
     *
     * @param       DOMNode $node XML-Node to output
     *
     * @return      string XML string
     *
     * @deprecated  Use BaseXmlParser::nodeToString()
     */
    protected function nodeToString(DOMNode $node)
    {
        return BaseXmlParser::nodeToString($node);
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>