<?php

/**
 * The PoliceReportDataRecord class is a concrete implementation of the ImperiaDataRecord base class.
 * It reflects a single dataset coming from the 'police-reports' content provider.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Imperia
 */
class PoliceReportDataRecord extends ImperiaDataRecord
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds the name of our title field.
     */
    const FIELD_TITLE = 'title';

    /**
     * Holds the name of our subtitle field.
     */
    const FIELD_SUBTITLE = 'subtitle';

    /**
     * Holds the name of our kicker field.
     */
    const FIELD_KICKER = 'kicker';

    /**
     * Holds the name of our text field.
     */
    const FIELD_TEXT = 'text';

    /**
     * Holds the name of our category field.
     */
    const FIELD_CATEGORY = 'category';
    
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds an array with known keys and xpath expressions as values.
     * This $fieldMap is used to evaluate and collect data from a given DOMDocument,
     * that has been initialized with imperia propetary xml.
     * 
     * @var     array 
     */
    protected static $fieldMap = array(
        'title'     => '/imperia/body/article/title',
        'subtitle'  => '/imperia/body/article/subtitle',
        'kicker'    => '/imperia/body/article/kicker',
        'text'      => '/imperia/body/article//paragraph/text',
        'category'  => '/imperia/head/categories/category',
        'directory' => '/imperia/head/directory',
        'filename'  => '/imperia/head/filename'
    );
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <ImportBaseDataRecord IMPL> ----------------------------
    
    /**
     * Returns the name of the field to use as the base for building our identifier.
     * 
     * @return      string
     * 
     * @see         ImportBaseDataRecord::getIdentifierFieldName()
     */
    protected function getIdentifierFieldName()
    {
        return 'title';
    }
    
    // ---------------------------------- </ImportBaseDataRecord IMPL> ---------------------------
    
        
    // ---------------------------------- <XmlBasedDataRecord IMPL> ------------------------------
    
    /**
     * Return an array holding fieldnames and corresponding xpath queries
     * that will be evaluated and mapped to the correlating field.
     * 
     * @return      array
     * 
     * @see         XmlBasedDataRecord::getFieldMap()
     */
    protected function getFieldMap()
    {
        return self::$fieldMap;
    }
    
    /**
     * Normalize the given xpath results.
     * 
     * @param       array $data Contains result from processing our field map.
     * 
     * @return      array
     * 
     * @see         XmlBasedDataRecord::normalizeData()
     */
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
    
    // ---------------------------------- </XmlBasedDataRecord IMPL> -----------------------------
}

?>