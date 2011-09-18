<?php

/**
 * The RssDataRecord class is a concrete implementation of the ImportBaseDataRecord base class.
 * It provides handling for rss item data.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <tom.anheyer@berlinonline.de>
 * @package         Import
 * @subpackage      Imperia
 */
class RssDataRecord extends ImportBaseDataRecord
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds the name of our author property.
     */
    const PROP_AUTHOR = 'author';
    
    /**
     * Holds the name of our timestamp property.
     */
    const PROP_TIMESTAMP = 'timestamp';
    
    // ---------------------------------- </CONSTANTS> -------------------------------------------
    
    
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds our rss item data.
     * 
     * @var         array
     */
    protected $data;
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------
    
    /**
     * Returns our author.
     * 
     * @return      string 
     */
    public function getAuthor()
    {
        return $this->data['author'];
    }
    
    /**
     * Returns our timestamp.
     * 
     * @return      string An ISO8601 formatted date string.
     */
    public function getTimestamp()
    {
        return $this->data['lastchanged']->format(DATE_ISO8601);
    }
    
    // ---------------------------------- </PUBLIC METHODS> --------------------------------------
    
    
    // ---------------------------------- <HYDRATE SETTERS> --------------------------------------
    
    /**
     * Sets our author during hydrate.
     * 
     * @param       string $author 
     */
    protected function setAuthor($author)
    {
        $this->data['author'] = $author;
    }

    /**
     * Sets our timestamp during hydrate.
     * 
     * @param       string $timestamp 
     */
    protected function setTimestamp($timestamp)
    {
        $this->data['lastchanged'] = new DateTime($timestamp);
        $this->data['timestamp'] = $this->data['lastchanged']->format('c');
    }
    
    // ---------------------------------- <HYDRATE SETTERS> --------------------------------------
     
    
    // ---------------------------------- <ImportBaseDataRecord OVERRIDES> -----------------------

    /**
     * Return an array holding property names of properties,
     * which we want to expose through our IDataRecord::toArray() method.
     *
     * @return      array
     */
    public function getExposedProperties()
    {
        return array_merge(
            parent::getExposedProperties(),
            array(
                self::PROP_AUTHOR, 
                self::PROP_TIMESTAMP
            )
        );
    }
    
    // ---------------------------------- </ImportBaseDataRecord OVERRIDES> ----------------------
    

    // ---------------------------------- <ImportBaseDataRecord IMPL> ----------------------------

    /**
     * Parse the incoming feed item data
     *
     * @param       mixed $data
     *
     * @return      array
     *
     * @see         ImportBaseDataRecord::parse()
     */
    protected function parseData($data)
    {
        $this->data = $data;

        $media = array();
        
        if (! empty($data['image']['url']))
        {
            $meta = array(
                'caption' => $data['teaser_text'],
                'title'   => $data['title']
            );
            
            if (($asset = ProjectAssetService::getInstance()->put($data['image']['url'], $meta)))
            {
                $media[] = $asset->getId();
            }
        }

        return array(
            self::PROP_IDENT    => $data['url'],
            self::PROP_TITLE    => $data['title'],
            self::PROP_CONTENT  => empty($data['html']) ? htmlspecialchars($data['teaser_text']) : $data['html'],
            self::PROP_MEDIA    => $media,
            self::PROP_GEO      => array()
        );
    }

    // ---------------------------------- </ImportBaseDataRecord IMPL> ---------------------------
}

?>