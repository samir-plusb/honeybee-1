<?php

abstract class XmlBasedDataRecord extends ImportBaseDataRecord
{
    /**
     * @return array<string, string>
     */
    abstract protected function getFieldMap();
    
    abstract protected function normalizeData(array $data);
    
    protected function parse($dataSrc)
    {
        $domDoc = $this->createDom($dataSrc);

        return $this->evaluateDocument($domDoc);
    }

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
    
    protected function evaluateDocument(DOMDocument $domDoc)
    {
        $rawData = $this->collectData($domDoc);

        return $this->normalizeData($rawData);
    }
    
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
}

?>