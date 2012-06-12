<?php

/**
 * The RssFeedParser is a concrete implementation of the BaseFeedParser base class.
 * It provides support for parsing Rss feeds.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <tom.anheyer@berlinonline.de>
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Parser/Feed
 */
class RssFeedParser extends BaseFeedParser
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     *
     * Namespace prefix for http://purl.org/rss/1.0/modules/content/
     * @var string
     */
    protected $nsContent;

    /**
     *
     * Namespace prefix for http://purl.org/dc/elements/1.1/
     * @var string
     */
    protected $nsElements;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <BaseFeedParser OVERRIDES> -----------------------------

    /**
     * Create a new RssFeedParser instance.
     *
     * @param        $doc
     */
    public function __construct(DOMDocument $doc)
    {
        parent::__construct($doc);

        $nsContent = $this->doc->documentElement->lookupPrefix('http://purl.org/rss/1.0/modules/content/');
        $nsContent = empty($nsContent) ? 'content' : $nsContent;
        $this->nsContent = $nsContent;
        $this->xpath->registerNamespace($nsContent, 'http://purl.org/rss/1.0/modules/content/');

        $nsElements = $this->doc->documentElement->lookupPrefix('http://purl.org/dc/elements/1.1/');
        $nsElements = empty($nsElements) ? 'dc' : $nsElements;
        $this->nsElements = $nsElements;
        $this->xpath->registerNamespace($nsElements, 'http://purl.org/dc/elements/1.1/');

        $this->parseFeed();
    }

    // ---------------------------------- </BaseFeedParser OVERRIDES> ----------------------------


    // ---------------------------------- <BaseFeedParser IMPL> ----------------------------------

    /**
     * Parse the given xml dom as rss.
     *
     * @return      array
     */
    protected function parseFeed()
    {
        $this->setTitle('/rss/channel/title');
        $this->setLink('/rss/channel/link');
        $this->setDescription('/rss/channel/description');
        $this->setCopyright('/rss/channel/copyright');
        $this->setTime('/rss/channel/pubDate');

        $nodeList = $this->query('/rss/channel/item');
        if ($nodeList)
        {
            foreach ($nodeList as $entryNode)
            {
                $item = new RssFeedItem($this, $entryNode);
                $this->addItem($item);
            }
        }
    }

    // ---------------------------------- </BaseFeedParser IMPL> ---------------------------------


    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    /**
     * Get namespace prefix for http://purl.org/dc/elements/1.1/.
     *
     * @return      string
     */
    public function getNamespaceElements()
    {
        return $this->nsElements;
    }

    /**
     * Get namespace prefix for http://purl.org/rss/1.0/modules/content/.
     *
     * @return      string
     */
    public function getNamespaceContent()
    {
        return $this->nsContent;
    }

    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------
}

?>