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
    
    protected function getFieldMap()
    {
        return self::$fieldMap;
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
