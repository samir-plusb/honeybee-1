<?php

/**
 * The AtomFeedItem is a concrete implementation of the BaseFeedItem base class
 * and is coded to support the concrete structure of atom.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <tom.anheyer@berlinonline.de>
 * @package         Import
 * @subpackage      Parser
 */
class AtomFeedItem extends BaseFeedItem
{
    // ---------------------------------- <BaseFeedItem IMPL> ------------------------------------
    
    /**
     * Parse xml node describing an item into our instance members.
     */
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
    
    // ---------------------------------- </BaseFeedItem IMPL> -----------------------------------
}

?>