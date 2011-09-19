<?php

/**
 * The IFeedParser interface defines the standard interface for parsing feeds inside this project.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Rss
 */
interface IFeedParser
{
    /**
     * Parse the given xml dom thereby treating it as a concrete feed (rss, atom...)
     * and return an assoc array reflecting the given feed's structure.
     *
     * @param       DOMDocument $doc
     * @param       int $source_index
     * 
     * @return      array
     */
    public function parseFeed(DOMDocument $doc);
}

?>