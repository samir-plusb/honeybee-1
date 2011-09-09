<?php

class RssDataRecord extends ImportBaseDataRecord
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds the name of our title field.
     */
    const FIELD_TITLE = 'title';

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
    
    /**
     * Holds our default category.
     */
    const DEFAULT_CATEGORY = 'rss/unknown';
    
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    
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
    
    /**
     * Parse the incoming ezcFeedEntryElement.
     * 
     * @param       mixed $data
     * 
     * @return      array
     * 
     * @throws      DataRecordException If a wrong(other than ezcFeedEntryElement) input data-type is given.
     * 
     * @see         http://ezcomponents.org/docs/api/trunk/Feed/ezcFeedEntryElement.html
     * @see         ImportBaseDataRecord::parse()
     */
    protected function parse($data)
    {
        /* @var $data ezcFeedEntryElement */
        $data;
        
        if (!($data instanceof ezcFeedEntryElement))
        {
            throw new DataRecordException(
                "Incoming data by a different type than expected 'ezcFeedEntryElement' encountered. " .
                "Instance of '" . get_class($data) . "' given. " .
                "The RssDataRecord only supports 'ezcFeedEntryElement' instances as it's data."
            );
        }
        
        $content = '';
        
        if (isset($data->Content) && isset($data->Content->encoded))
        {
            $content = $data->Content->encoded->__toString();
        }
        
        $categories = array();
        
        if (!empty($data->category))
        {
            foreach ($data->category as $category)
            {
                $categories[] = $category->term;
            }
        }
        
        $category = self::DEFAULT_CATEGORY;
        
        if (!empty ($categories))
        {
            $category = implode('/', $categories);
        }
        
        $description = isset($data->description) ? $data->description->__toString() : '';
        
        return array(
            self::FIELD_TITLE    => $data->title->__toString(),
            self::FIELD_TEXT     => $content,
            self::FIELD_CATEGORY => $category,
            self::FIELD_KICKER   => $description
        );
    }
    
    // ---------------------------------- </ImportBaseDataRecord IMPL> ---------------------------
}

?>