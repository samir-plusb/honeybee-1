<?php

/**
 * The RssFeedItem is a concrete implementation of the BaseFeedItem base class
 * and is coded to support the concrete structure of rss 2.0.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <tom.anheyer@berlinonline.de>
 * @package         Import
 * @subpackage      Parser/Feed
 */
class RssFeedItem extends BaseFeedItem
{
    // ---------------------------------- <BaseFeedItem IMPL> ------------------------------------

    /**
     * Parse xml node describing an item into our instance members.
     */
    protected function parseItem()
    {
        $this->setId('guid');
        $this->setTitle('title');
        $this->setLink('link');
        $this->setTime('pubDate');
        $this->setImage('enclosure[@type="image/jpeg"]/@url');

        if (! $this->time)
        {
            $this->setTime($this->feed->getNamespaceElements().':date');
            if (! $this->time)
            {
                $this->time = $this->feed->getTime();
            }
        }

        $this->setText('description');
        $this->setHtml($this->feed->getNamespaceContent().':encoded');
        if (empty($this->text))
        {
            $this->text = strip_tags($this->html);
        }
    }

    // ---------------------------------- </BaseFeedItem IMPL> -----------------------------------
}

?>