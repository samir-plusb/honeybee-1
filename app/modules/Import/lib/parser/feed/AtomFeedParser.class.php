<?php

/**
 * The AtomFeedParser is a concrete implementation of the BaseFeedParser base class.
 * It provides support for parsing Atom feeds.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <tom.anheyer@berlinonline.de>
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Parser/Feed
 */
class AtomFeedParser extends BaseFeedParser
{
    // ---------------------------------- <BaseFeedParser IMPL> ----------------------------------

    /**
     * Parse the given xml dom as atom.
     *
     * @param       DOMDocument $doc
     *
     * @return      boolean
     */
    public function parseFeed()
    {
        if ($this->isParseable())
        {
            return TRUE;
        }

        $this->xpath->registerNamespace('a', 'http://www.w3.org/2005/Atom');

        $this->setTitle('/a:feed/a:title');
        $this->setDescription('/a:feed/a:subtitle');
        $this->setLink('/a:feed/a:link/@href');
        $this->setCopyright('/a:feed/a:rights');

        $nodeList = $this->query('/a:feed/a:entry');
        foreach ($nodeList as $entryNode)
        {
            $itemData = $this->parseItem($entryNode, $feedData);
            if ($itemData['time'])
            {
                $feedData['items'][] = $itemData;
            }
        }

        return TRUE;
    }

    // ---------------------------------- <BaseFeedParser IMPL> ----------------------------------
}

?>