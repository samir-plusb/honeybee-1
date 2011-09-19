<?php

/**
 * The AtomFeedParser is a concrete implementation of the BaseFeedParser base class.
 * It provides support for parsing Atom feeds.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Parser
 */
class AtomFeedParser extends BaseFeedParser
{
    // ---------------------------------- <IFeedParser IMPL> -------------------------------------
    
    /**
     * Parse the given xml dom as atom.
     *
     * @param       DOMDocument $doc
     * 
     * @return      array
     */
    public function parseFeed(DOMDocument $doc)
    {
        $feedData = $this->initFeedData();
        $xpath = new DOMXpath($doc);
        $xpath->registerNamespace('a', 'http://www.w3.org/2005/Atom');

        $nodeList = $xpath->query('/a:feed/a:title');
        if ($nodeList && $nodeList->length > 0)
        {
            $feedData['title'] = $nodeList->item(0)->nodeValue;
        }
        
        $nodeList = $xpath->query('/a:feed/a:subtitle');
        if ($nodeList && $nodeList->length > 0)
        {
            $feedData['description'] = $nodeList->item(0)->nodeValue;
        }
        
        $nodeList = $xpath->query('/a:feed/a:link/@href');
        if ($nodeList && $nodeList->length > 0)
        {
            $feedData['link'] = $nodeList->item(0)->nodeValue;
        }
        
        $nodeList = $xpath->query('/a:feed/a:rights');
        if ($nodeList && $nodeList->length > 0)
        {
            $feedData['copyright'] = $nodeList->item(0)->nodeValue;
        }

        $nodeList = $xpath->query('/a:feed/a:entry');
        foreach ($nodeList as $entryNode)
        {
            $itemData = $this->initItemData();
            
            $list = $xpath->query('a:title', $entryNode);
            if ($list && $list->length > 0)
            {
                $itemData['title'] = $list->item(0)->nodeValue;
            }
            
            $list = $xpath->query('a:link/@href', $entryNode);
            if ($list && $list->length > 0)
            {
                $itemData['url'] = $list->item(0)->nodeValue;
            }
            
            $list = $xpath->query('a:updated', $entryNode);
            if ($list && $list->length > 0)
            {
                $itemData['lastchanged'] = new DateTime($list->item(0)->nodeValue);
                $itemData['timestamp'] = $itemData['lastchanged']->format('c');
            }
            
            $list = $xpath->query('a:summary', $entryNode);
            if ($list && $list->length > 0)
            {
                $itemData['teaser_text'] = strip_tags($list->item(0)->nodeValue);
            }
            
            $list = $xpath->query('a:content', $entryNode);
            if ($list && $list->length > 0)
            {
                $itemData['html'] = $list->item(0)->nodeValue;
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