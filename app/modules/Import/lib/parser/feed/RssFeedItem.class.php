<?php

/**
 * The BaseFeedItem provides basic functionality that usefull to all xml based feed item implementations.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Parser
 */
class RssFeedItem extends BaseFeedItem
{
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
}