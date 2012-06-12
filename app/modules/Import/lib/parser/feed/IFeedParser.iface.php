<?php

/**
 * The IFeedParser interface defines the standard interface for parsing feeds inside this project.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Parser/Feed
 */
interface IFeedParser
{
    /**
     * Returns the feed's title.
     *
     * @return      string
     */
    public function getTitle();

    /**
     * Returns the feed's description.
     *
     * @return      string
     */
    public function getDescription();

    /**
     * Returns the feed's link.
     *
     * @return      string
     */
    public function getLink();

    /**
     * Returns the feed's time.
     *
     * @return      DateTime
     */
    public function getTime();

    /**
     * Returns the feed's copyright.
     *
     * @return      string
     */
    public function getCopyright();

    /**
     * Returns the feed's items.
     *
     * @return      array
     */
    public function getItems();
}

?>