<?php

class ImperiaDataRecord extends XmlBasedDataRecord
{
    const FIELD_TITLE = 'title';

    const FIELD_SUBTITLE = 'subtitle';

    const FIELD_KICKER = 'kicker';

    const FIELD_TEXT = 'text';

    const FIELD_CATEGORY = 'category';

    protected static $fieldMap = array(
        'title'     => '/imperia/body/article/title',
        'subtitle'  => '/imperia/body/article/subtitle',
        'kicker'    => '/imperia/body/article/kicker',
        'text'      => '/imperia/body/article//paragraph/text',
        'category'  => '/imperia/head/categories/category',
        'directory' => '/imperia/head/directory',
        'filename'  => '/imperia/head/filename'
    );

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
        $rawData = $this->collectData($domDoc);

        return $this->normalizeData($rawData);
    }

    protected function collectData(DOMDocument $domDoc)
    {
        $xPath = new DOMXPath($domDoc);
        $data = array();

        foreach (self::$fieldMap as $dataKey => $dataExpr)
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

    protected function normalizeData(array $mappedData)
    {
        $data = array();

        // Handle our simple fields, hence stuff we can directly use without further processing.
        $simpleFields = array('title', 'subtitle', 'kicker');

        foreach ($simpleFields as $simpleField)
        {
            if (isset($mappedData[$simpleField]))
            {
                $data[$simpleField] = trim($mappedData[$simpleField]);
            }
        }

        // Handle our article paragraphs.
        $data['text'] = $mappedData['text'];

        if ($mappedData['text'] instanceof DOMNodeList)
        {
            $data['text'] = array();

            for ($i = 0; $i < $mappedData['text']->length; $i++)
            {
                $data['text'][] = trim($mappedData['text']->item($i)->nodeValue);
            }
        }

        // Handle our article category.
        $breadcrumb = array();

        if ($mappedData['category'] instanceof DOMNodeList)
        {
            for ($i = 0; $i < $mappedData['category']->length; $i++)
            {
                $breadcrumb[] = trim($mappedData['category']->item($i)->nodeValue);
            }
        }
        else
        {
            $breadcrumb = array($mappedData['category']);
        }

        $data['category'] = sprintf('// %s', join(' // ', $breadcrumb));

        // Handle our article link.
        $data['link'] = sprintf(
            "http://www.berlin.de%s/%s",
            $mappedData['directory'],
            $mappedData['filename']
        );

        return $data;
    }
}

?>
