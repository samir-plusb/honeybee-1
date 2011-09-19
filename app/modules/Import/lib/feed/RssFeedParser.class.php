<?php

/**
 * The RssFeedParser is a concrete implementation of the BaseFeedParser base class.
 * It provides support for parsing Rss feeds.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Rss
 */
class RssFeedParser extends BaseFeedParser
{
    // ---------------------------------- <IFeedParser IMPL> -------------------------------------
    
    /**
     * Parse the given xml dom as rss.
     *
     * @param       DOMDocument $doc
     * 
     * @return      array
     */
    public function parseFeed(DOMDocument $doc)
    {
        $xpath = new DOMXpath($doc);

        $nsContent = $doc->documentElement->lookupPrefix('http://purl.org/rss/1.0/modules/content/');
        $nsContent = empty($nsContent) ? 'content' : $nsContent;
        $xpath->registerNamespace($nsContent, 'http://purl.org/rss/1.0/modules/content/');

        $nsElements = $doc->documentElement->lookupPrefix('http://purl.org/dc/elements/1.1/');
        $nsElements = empty($nsElements) ? 'dc' : $nsElements;
        $xpath->registerNamespace($nsElements, 'http://purl.org/dc/elements/1.1/');

        $feedData = $this->initFeedData();

        $nodeList = $xpath->query('/rss/channel/title');
        if ($nodeList && $nodeList->length > 0)
        {
            $feedData['title'] = $nodeList->item(0)->nodeValue;
        }
        
        $nodeList = $xpath->query('/rss/channel/link');
        if ($nodeList && $nodeList->length > 0)
        {
            $feedData['link'] = $nodeList->item(0)->nodeValue;
        }
        
        $nodeList = $xpath->query('/rss/channel/description');
        if ($nodeList && $nodeList->length > 0)
        {
            $feedData['description'] = $nodeList->item(0)->nodeValue;
        }
        
        $nodeList = $xpath->query('/rss/channel/copyright');
        if ($nodeList && $nodeList->length > 0)
        {
            $feedData['copyright'] = $nodeList->item(0)->nodeValue;
        }
        
        $nodeList = $xpath->query('/rss/channel/pubDate');
        if ($nodeList && $nodeList->length > 0)
        {
            $feedData['lastchanged'] = new DateTime($nodeList->item(0)->nodeValue);
        }
        else
        {
            $feedData['lastchanged'] = new DateTime();
        }

        $nodeList = $xpath->query('/rss/channel/item');
        
        foreach ($nodeList as $entryNode)
        {
            $itemData = $this->initItemData();
            
            $list = $xpath->query('title', $entryNode);
            if ($list && $list->length > 0)
            {
                $itemData['title'] = $list->item(0)->nodeValue;
            }
            
            $list = $xpath->query('link', $entryNode);
            if ($list && $list->length > 0)
            {
                $itemData['url'] = $list->item(0)->nodeValue;
            }
            
            $list = $xpath->query('pubDate', $entryNode);
            if ($list && $list->length > 0)
            {
                $itemData['lastchanged'] = new DateTime($list->item(0)->nodeValue);
                $itemData['timestamp'] = $itemData['lastchanged']->format('c');
            }
            else
            {
                $list = $xpath->query($nsElements.':date', $entryNode);
                if ($list && $list->length > 0)
                {
                    $itemData['lastchanged'] = new DateTime($list->item(0)->nodeValue);
                    $itemData['timestamp'] = $itemData['lastchanged']->format('c');
                }
                else
                {
                    $itemData['lastchanged'] = $feedData['lastchanged'];
                    $itemData['timestamp'] = $itemData['lastchanged']->format('c');
                }
            }
            
            $list = $xpath->query('description', $entryNode);
            if ($list && $list->length > 0)
            {
                $itemData['teaser_text'] = $list->item(0)->nodeValue;
                if (empty ($itemData['html']))
                {
                    $itemData['html'] = htmlspecialchars($itemData['teaser_text']);
                }
            }
            
            $list = $xpath->query($nsContent.':encoded', $entryNode);
            if ($list && $list->length > 0)
            {
                $itemData['html'] = $list->item(0)->nodeValue;
                if (empty ($itemData['teaser_text']))
                {
                    $itemData['teaser_text'] = strip_tags($itemData['html']);
                }
            }
            
            $list = $xpath->query('enclosure[@type="image/jpeg"]/@url', $entryNode);
            if ($list && $list->length > 0)
            {
                $itemData['image']['url'] = $list->item(0)->nodeValue;
            }
            
            if ($itemData['timestamp'])
            {
                $feedData['items'][] = $itemData;
            }
        }

        return $feedData;
    }
    
    // ---------------------------------- </IFeedParser IMPL> ------------------------------------
}

?>