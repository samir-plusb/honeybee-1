<?php

/**
 * The BaseFeedParser is a concrete implementation of the IFeedParser interface
 * and provides basic functionality that usefull to all concrete IFeedParser implementations.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Rss
 */
abstract class BaseFeedParser implements IFeedParser
{
    // ---------------------------------- <BASE WORKING METHODS> ---------------------------------
    
    /**
     * initialize feed data array
     *
     * @return      array
     */
    protected function initFeedData()
    {
        return array
        (
            'title'       => '',
            'description' => '',
            'link'        => '',
            'copyright'   => '',
            'items'       => array()
        );
    }

    /**
     * initialize a feed item data array
     *
     * @return      array
     */
    protected function initItemData()
    {
        return array(
            'author'      => '',
            'title'       => '',
            'link'        => '',
            'timestamp'   => '',
            'datetime'    => '',
            'teaser_text' => '',
            'html'        => ''
        );
    }
    
    // ---------------------------------- </BASE WORKING METHODS> --------------------------------
}

?>