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
 * @subpackage      Base/DataRecord
 */
abstract class XmlBasedDataRecord extends ImportBaseDataRecord
{
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
     * @see         ImportBaseDataRecord::parse()
     * 
     * @uses        XmlBasedDataRecord::createDom()
     * @uses        XmlBasedDataRecord::evaluateDocument()
     */
    protected function parse($dataSrc)
    {
        $domDoc = $this->createDom($dataSrc);

        return $this->evaluateDocument($domDoc);
    }
    
    // ---------------------------------- </ImportBaseDataRecord IMPL> ---------------------------
    
    
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
    protected function evaluateDocument(DOMDocument $domDoc)
    {
        $rawData = $this->collectData($domDoc);

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
            $value = FALSE;

            if (0 < $nodeList->length)
            {
                $value = (1 === $nodeList->length) ? trim($nodeList->item(0)->nodeValue) : $nodeList;
            }

            $data[$dataKey]= $value;
        }

        return $data;
    }
    
    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>