<?php

/**
 * The RssDataSource class is a concrete implementation of the ImportBaseDataSource base class.
 * It provides fetching rss based data from a given rss source url.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Rss
 */
class RssDataSource extends ImportBaseDataSource
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds a reference to the ezcFeed instance,
     * that we use to parse the given rss.
     * 
     * @see         http://ezcomponents.org/docs/api/trunk/Feed/ezcFeed.html
     * 
     * @var         ezcFeed
     */
    private $rssFeed;
    
    /**
     * Holds our current position, 
     * while iterating over our feed items.
     * 
     * @var         int
     */
    private $cursorPos;
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <ImportBaseDataSource IMPL> ----------------------------
    
    /**
     * Initialize our datasource.
     * 
     * @see         ImportBaseDataSource::init()
     */
    protected function init()
    {
        $this->rssFeed = ezcFeed::parse(
            $this->config->getSetting(RssDataSourceConfig::CFG_RSS_URL)
        );

        $this->cursorPos = -1;
    }
    
    /**
     * Forward our cursor, hence move to our next $documentId.
     * 
     * @return      boolean
     * 
     * @see         ImportBaseDataSource::forwardCursor()
     */
    protected function forwardCursor()
    {
        if ($this->cursorPos < count($this->rssFeed->item) - 1)
        {
            $this->cursorPos++;

            return TRUE;
        }

        return FALSE;
    }
    
    /**
     * Forward our cursor, hence move to our next $documentId.
     * 
     * @return      array
     * 
     * @throws      DataSourceException If the cursor is invalid.
     * 
     * @see         ImportBaseDataSource::fetchData()
     * 
     * @uses        ImperiaDataSource::loadDocumentById()
     */
    protected function fetchData()
    {
        if (!isset ($this->rssFeed->item[$this->cursorPos]))
        {
            throw new DataSourceException(
                "The internal cursor is out of range and points to no existing feed item."
            );
        }
        return $this->rssFeed->item[$this->cursorPos];
    }
    
    // ---------------------------------- </ImportBaseDataSource IMPL> ---------------------------
}

?>