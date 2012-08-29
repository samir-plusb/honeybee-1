<?php

/**
 * The TheaterXmlParser class is an abstract implementation of the IXmlParser interface.
 * We are not extending the BaseXmlParser class here, because we will want to refactor that one first,
 * before baking in the old interface all over the place.
 *
 * @version         $Id: ImperiaXmlParser.class.php 1299 2012-06-12 16:09:14Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 * @subpackage      Import/Tip
 */
class TipRestaurantXmlParser implements IXmlParser
{
    // ---------------------------------- <BaseXmlParser IMPL> -----------------------------------

    public function parseXml($xmlSource)
    {
        $document = new DOMDocument('1.0', 'utf-8');
        if (is_file($xmlSource))
        {
            $document->load($xmlSource);
        }
        else
        {
            $document->loadXML($xmlSource);
        }
        return $this->processDocument($document);
    }

    protected function processDocument(DOMDocument $document)
    {
        $xpath = new DOMXPath($document);
        $nsUri = $document->lookupNamespaceUri($document->namespaceURI);
        $xpath->registerNamespace('x', $nsUri);

        $columns = array();
        $metaDataNode = $xpath->query('//x:METADATA')->item(0);
        foreach ($metaDataNode->getElementsByTagNameNs($nsUri, 'FIELD') as $fieldNode)
        {
            $columns[] = $fieldNode->getAttribute('NAME');
        }

        $data = array();
        $resultNode = $xpath->query('//x:RESULTSET')->item(0);
        foreach ($resultNode->getElementsByTagNameNs($nsUri, 'ROW') as $rowNode)
        {
            $colIdx = 0;
            $rowData = array();
            foreach ($rowNode->getElementsByTagNameNs($nsUri, 'COL') as $columnNode)
            {
                $dataNode = $columnNode->getElementsByTagNameNs($nsUri, 'DATA')->item(0);
                $rowData[$columns[$colIdx]] = trim($dataNode->nodeValue);
                $colIdx++;
            }
            $data[] = $rowData;            
        }

        return $data;
    }

    // ---------------------------------- </BaseXmlParser IMPL> ----------------------------------
}

?>