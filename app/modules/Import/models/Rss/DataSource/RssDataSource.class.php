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
     * @var         array parsed feed
     */
    private $feedData;

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
     *
     * @throws      IOException on problems with temporary file
     * @throws      DataSourceException on curl errors
     */
    protected function init()
    {
        $this->feedData = $this->parse(
            $this->getRawContent()
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
        $this->cursorPos ++;
        return isset($this->feedData['items'][$this->cursorPos]);
    }

    /**
     * get data item at current position
     *
     * @return      array
     *
     * @see         ImportBaseDataSource::fetchData()
     */
    protected function fetchData()
    {
        return $this->feedData['items'][$this->cursorPos];
    }

    // ---------------------------------- </ImportBaseDataSource IMPL> ---------------------------
    
    
    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    /**
     * Parse the incoming feed content
     *
     * @param       string $feedContent raw feed file contents
     *
     * @return      array
     *
     * @throws      DataSourceException If parsing fails due to bad content.
     *
     */
    protected function parse($feedContent)
    {
        $feedDoc = $this->createFeedDocument(
            $this->getRawContent()
        );

        $feed = array();
        $root = $feedDoc->documentElement;
        $parser = NULL;
        
        switch ($feedDoc->documentElement->tagName)
        {
            case 'rss':
                $parser = new RssFeedParser();
                break;
            case 'feed':
                $parser = new AtomFeedParser();
                break;
            default:
                throw new DataSourceException('Feed type "'.$feedDoc->documentElement->tagName.'" not implemented: '.
                    $this->config->getSetting(RssDataSourceConfig::CFG_RSS_URL));
        }

        return $parser->parseFeed($feedDoc);
    }
    
    /**
     * Creates a DOMDocument from the given raw feed content.
     * 
     * @param       string $content
     * 
     * @return      DOMDocument 
     */
    protected function createFeedDocument($content)
    {
        libxml_clear_errors();
        $doc = new DOMDocument();
        
        if (! $doc || ! $doc->loadXML($content))
        {
            $xmlerror = libxml_get_last_error();
            
            if ($xmlerror)
            {
                throw new DataSourceException($xmlerror->file.' : '.$xmlerror->message);
            }
            else
            {
                throw new DataSourceException('Can not parse feed: '.
                    $this->config->getSetting(RssDataSourceConfig::CFG_RSS_URL)
                );
            }
        }
        
        return $doc;
    }
    
    /**
     * Returns the raw feed content. (xml string)
     *
     * @return      string
     */
    protected function getRawContent()
    {
        $feedUri = $this->config->getSetting(RssDataSourceConfig::CFG_RSS_URL);
        
        if (is_file($feedUri) && is_readable($feedUri))
        {
            $rawContent = file_get_contents($feedUri);
        }
        else
        {
            $rawContent = $this->loadFeedByUrl($feedUri);
        }
        
        return $rawContent;
    }
    
    /**
     * Load and return the feed content for a given feed url.
     * 
     * @param       string $url
     * 
     * @return      string
     */
    protected function loadFeedByUrl($url)
    {
        $tmpname = tempnam('/var/tmp', basename(__FILE__));
        $fd = fopen($tmpname, 'w');

        if (! $tmpname || ! $fd)
        {
            throw new IOException('Can not open temporary file: '.$tempname);
        }

        $curlHandle = ProjectCurl::create();
        curl_setopt($curlHandle, CURLOPT_URL, $this->config->getSetting(RssDataSourceConfig::CFG_RSS_URL));
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 0);
        curl_setopt($curlHandle, CURLOPT_FILE, $fd);

        if (! curl_exec($curlHandle) || 200 != curl_getinfo($curlHandle, CURLINFO_HTTP_CODE))
        {
            fclose($fd);
            unlink($tmpname);
            throw new DataSourceException('Curl failed: '.curl_error($curlHandle), curl_errno($curlHandle));
        }

        curl_close($curlHandle);
        fclose($fd);
        $rawContent = file_get_contents($tmpname);
        unlink($tmpname);

        return $rawContent;
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>