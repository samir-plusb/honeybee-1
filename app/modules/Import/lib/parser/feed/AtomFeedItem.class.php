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
class AtomFeedItem extends BaseFeedItem
{
    protected function parseItem()
    {
        $this->setId('a:id');
        $this->setTitle('a:title');
        $this->setLink('a:link/@href');
        $this->setTime('a:updated');
        $this->setHtml('a:summary');
        $this->text = strip_tags($this->html);
        $this->setHtml('a:content');
    }
}