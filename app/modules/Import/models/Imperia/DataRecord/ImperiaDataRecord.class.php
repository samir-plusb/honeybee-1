<?php

class ImperiaDataRecord extends ImportBaseDataRecord
{
    protected function parse($dataSrc)
    {
        $domDoc = null;
        
        if ($dataSrc instanceof DOMDocument)
        {
            $domDoc = $dataSrc;
        }
        else
        {
            $domDoc = $this->createDom($dataSrc);
        }
        
        return $this->evaluateDocument($domDoc);
    }
    
    protected function createDom($xmlString)
    {
        libxml_clear_errors();
        $domDoc = new DOMDocument();
        
        if (!$domDoc->loadXML($xmlString))
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
        $title = $this->simpleXpath('//article/title', $domDoc);
        $kicker = $this->simpleXpath('//article/kicker', $domDoc);
        $subtitle = $this->simpleXpath('//article/subtitle', $domDoc);
        $filename = $this->simpleXpath('//head/filename', $domDoc);
        $directory = $this->simpleXpath('//head/directory', $domDoc);

        $nodeList = $this->xpath->query('//article//paragraph/text');
        
        for ($i = 0; $i < $nodeList->length; $i ++)
        {
            $paragraphs[] = $nodeList->item($i)->nodeValue;
        }

        $breadcrumb = array();
        $nodeList =  $this->xpath->query('//categories/category');
        
        for ($i = $nodeList->length - 1; $i >= 0; $i --)
        {
            $breadcrumb[] = $nodeList->item($i)->nodeValue;
        }

        return array
        (
            'title'    => $title,
            'subtitle' => $subtitle,
            'kicker'   => $kicker,
            'text'     => $paragraphs,
            'category' => '// '. join(' // ', $breadcrumb),
            'link'     => "http://www.berlin.de${directory}/${filename}"
        );
    }
    
    protected function simpleXpath($xpath, DOMNode $contextNode)
    {
        $nodeList = $this->xpath->query($xpath, $contextNode);
        
        if (!$nodeList instanceof DOMNodeList || $nodeList->length != 1)
        {
            return FALSE;
        }
        
        return $nodeList->item(0)->nodeValue;
    }
}

?>
