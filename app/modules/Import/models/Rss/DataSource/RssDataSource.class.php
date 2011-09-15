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
        $rawContent = $this->getRawContent();
        if (! $rawContent)
        {
            throw new DataSourceException('Can not get content from: '.$fname);
        }

        $this->feedData = $this->parse($rawContent);
        $this->cursorPos = -1;
    }

    /**
     * get raw feed file contents
     *
     * @return string
     */
    protected function getRawContent()
    {
        $fname = $this->config->getSetting(RssDataSourceConfig::CFG_RSS_URL);
        if (is_file($fname))
        {
            $rawContent = file_get_contents($fname);
        }
        else
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
        }
        return $rawContent;
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


    /**
     * initialize feed data array
     *
     * @return array
     */
    protected function _initFeedData()
    {
        return array
        (
            'title' => '',
            'description' => '',
            'link' => '',
            'copyright' => '',
            'items' => array()
        );
    }

    /**
     * initialize a feed item data array
     *
     * @return array
     */
    protected function _initItemData()
    {
        return array(
            'author' => '',
            'title' => '',
            'link' => '',
            'timestamp' => '',
            'datetime' => '',
            'teaser_text' => '',
            'html' => ''
        );
    }


    /**
     * parse a ATOM Feed (tagesschau.de)
     *
     * @param DOMDocument $doc
     * @param int $source_index
     * @return array
     */
    protected function parseAtom(DOMDocument $doc)
    {
        $info = $this->_initFeedData();
        $xpath = new DOMXpath($doc);
        $xpath->registerNamespace('a', 'http://www.w3.org/2005/Atom');

        $el = $xpath->query('/a:feed/a:title');
        if ($el && $el->length > 0)
        {
            $info['title'] = $el->item(0)->nodeValue;
        }
        $el = $xpath->query('/a:feed/a:subtitle');
        if ($el && $el->length > 0)
        {
            $info['description'] = $el->item(0)->nodeValue;
        }
        $el = $xpath->query('/a:feed/a:link/@href');
        if ($el && $el->length > 0)
        {
            $info['link'] = $el->item(0)->nodeValue;
        }
        $el = $xpath->query('/a:feed/a:rights');
        if ($el && $el->length > 0)
        {
            $info['copyright'] = $el->item(0)->nodeValue;
        }

        $el = $xpath->query('/a:feed/a:entry');
        foreach ($el as $it)
        {
            $item = $this->_initItemData();
            $e = $xpath->query('a:title', $it);
            if ($e && $e->length > 0)
            {
                $item['title'] = $e->item(0)->nodeValue;
            }
            $e = $xpath->query('a:link/@href', $it);
            if ($e && $e->length > 0)
            {
                $item['url'] = $e->item(0)->nodeValue;
            }
            $e = $xpath->query('a:updated', $it);
            if ($e && $e->length > 0)
            {
                $item['lastchanged'] = new DateTime($e->item(0)->nodeValue);
                $item['timestamp'] = $item['lastchanged']->format('c');
            }
            $e = $xpath->query('a:summary', $it);
            if ($e && $e->length > 0)
            {
                $item['teaser_text'] = strip_tags($e->item(0)->nodeValue);
            }
            $e = $xpath->query('a:content', $it);
            if ($e && $e->length > 0)
            {
                $item['html'] = $e->item(0)->nodeValue;
            }

            if ($item['timestamp'])
            {
                $info['items'][] = $item;
            }
        }
        return $info;
    }

    /**
     * parse a xml dom as rss
     *
     * @param DOMDocument $doc
     * @param int $source_index
     * @return array
     */
    protected function parseRss(DOMDocument $doc)
    {
        $xpath = new DOMXpath($doc);

        $ns_content = $doc->documentElement->lookupPrefix('http://purl.org/rss/1.0/modules/content/');
        $ns_content = empty($ns_content) ? 'content' : $ns_content;
        $xpath->registerNamespace($ns_content, 'http://purl.org/rss/1.0/modules/content/');

        $ns_dc = $doc->documentElement->lookupPrefix('http://purl.org/dc/elements/1.1/');
        $ns_dc = empty($ns_dc) ? 'dc' : $ns_dc;
        $xpath->registerNamespace($ns_dc, 'http://purl.org/dc/elements/1.1/');

        $info = $this->_initFeedData();

        $el = $xpath->query('/rss/channel/title');
        if ($el && $el->length > 0)
        {
            $info['title'] = $el->item(0)->nodeValue;
        }
        $el = $xpath->query('/rss/channel/link');
        if ($el && $el->length > 0)
        {
            $info['link'] = $el->item(0)->nodeValue;
        }
        $el = $xpath->query('/rss/channel/description');
        if ($el && $el->length > 0)
        {
            $info['description'] = $el->item(0)->nodeValue;
        }
        $el = $xpath->query('/rss/channel/copyright');
        if ($el && $el->length > 0)
        {
            $info['copyright'] = $el->item(0)->nodeValue;
        }
        $el = $xpath->query('/rss/channel/pubDate');
        if ($el && $el->length > 0)
        {
            $info['lastchanged'] = new DateTime($el->item(0)->nodeValue);
        }
        else
        {
            $info['lastchanged'] = new DateTime();
        }

        $el = $xpath->query('/rss/channel/item');
        foreach ($el as $it)
        {
            $item = $this->_initItemData();
            $e = $xpath->query('title', $it);
            if ($e && $e->length > 0)
            {
                $item['title'] = $e->item(0)->nodeValue;
            }
            $e = $xpath->query('link', $it);
            if ($e && $e->length > 0)
            {
                $item['url'] = $e->item(0)->nodeValue;
            }
            $e = $xpath->query('pubDate', $it);
            if ($e && $e->length > 0)
            {
                $item['lastchanged'] = new DateTime($e->item(0)->nodeValue);
                $item['timestamp'] = $item['lastchanged']->format('c');
            }
            else
            {
                $e = $xpath->query($ns_dc.':date', $it);
                if ($e && $e->length > 0)
                {
                    $item['lastchanged'] = new DateTime($e->item(0)->nodeValue);
                    $item['timestamp'] = $item['lastchanged']->format('c');
                }
                else
                {
                    $item['lastchanged'] = $info['lastchanged'];
                    $item['timestamp'] = $item['lastchanged']->format('c');
                }
            }
            $e = $xpath->query('description', $it);
            if ($e && $e->length > 0)
            {
                $item['teaser_text'] = $e->item(0)->nodeValue;
                if (empty ($item['html']))
                {
                    $item['html'] = htmlspecialchars($item['teaser_text']);
                }
            }
            $e = $xpath->query($ns_content.':encoded', $it);
            if ($e && $e->length > 0)
            {
                $item['html'] = $e->item(0)->nodeValue;
                if (empty ($item['teaser_text']))
                {
                    $item['teaser_text'] = strip_tags($item['html']);
                }
            }
            $e = $xpath->query('enclosure[@type="image/jpeg"]/@url', $it);
            if ($e && $e->length > 0)
            {
                $item['image']['url'] = $e->item(0)->nodeValue;
            }
            if ($item['timestamp'])
            {
                $info['items'][] = $item;
            }
        }

        return $info;
    }

    /**
     * Parse the incoming feed content
     *
     * @param       string $content raw feed file contents
     *
     * @return      array
     *
     * @throws      DataRecordException If a wrong(other than ezcFeedEntryElement) input data-type is given.
     *
     * @see         ImportBaseDataRecord::parse()
     */
    protected function parse($content)
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
                    $this->config->getSetting(RssDataSourceConfig::CFG_RSS_URL));
            }
        }

        $feed = array();
        $root = $doc->documentElement;
        switch ($doc->documentElement->tagName)
        {
            case 'rss':
                $feed = $this->parseRss($doc);
                break;
            case 'feed':
                $feed = $this->parseAtom($doc);
                break;
            default:
                throw new DataSourceException('Feed type "'.$doc->documentElement->tagName.'" not implemented: '.
                    $this->config->getSetting(RssDataSourceConfig::CFG_RSS_URL));
        }

        return $feed;
    }

    // ---------------------------------- </ImportBaseDataSource IMPL> ---------------------------
}

?>