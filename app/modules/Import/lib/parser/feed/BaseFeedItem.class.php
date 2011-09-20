<?php
/**
 *
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer
 * @package         Lib
 * @subpackage      Parser
 *
 */
abstract class BaseFeedItem
{
    /**
     * @var string
     */
    protected  $id;
    /**
     * @var string
     */
    protected $author;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $link;

    /**
     * @var DateTime
     */
    protected $time;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $html;

    /**
     * @var string
     */
    protected $image;

    /**
     *
     * @var BaseFeedParser
     */
    protected $feed;

    /**
     *
     * context node for xpath queries
     * @var DOMNode
     */
    protected $contextnode;

    /**
     *
     * Enter description here ...
     * @param BaseFeedParser $feed
     * @param DOMNode $contextnode
     */
    public function __construct(BaseFeedParser $feed, DOMNode $contextnode)
    {
        $this->feed = $feed;
        $this->contextnode = $contextnode;
        $this->parseItem();
    }

    /**
     *
     * parse xml node describing an item into our instance members
     */
    abstract protected function parseItem();

    public function getId()
    {
        return empty($this->id) ? $this->getLink() : $this->id;
    }


    /**
     * set feed description by xpath expression
     *
     * @param string $expression xpath expression
     */
    protected function setId($expression)
    {
        $nodeList = $this->query($expression);
        if ($nodeList)
        {
            $this->id = $nodeList->item(0)->nodeValue;
        }
    }


    /**
     * Get feed item author.
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * set feed description by xpath expression
     *
     * @param string $expression xpath expression
     */
    protected function setAuthor($expression)
    {
        $nodeList = $this->query($expression);
        if ($nodeList)
        {
            $this->author = $nodeList->item(0)->nodeValue;
        }
    }


    /**
     * Get feed item title
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * set feed description by xpath expression
     *
     * @param string $expression xpath expression
     */
    protected function setTitle($expression)
    {
        $nodeList = $this->query($expression);
        if ($nodeList)
        {
            $this->title = $nodeList->item(0)->nodeValue;
        }
    }

    /**
     * Get feed item link or url.
     *
     * @return string
     */
    public function getLink() {
        return $this->link;
    }

    /**
     * set feed description by xpath expression
     *
     * @param string $expression xpath expression
     */
    protected function setLink($expression)
    {
        $nodeList = $this->query($expression);
        if ($nodeList)
        {
            $this->link = $nodeList->item(0)->nodeValue;
        }
    }

    /**
     * Get feed item timestamp
     *
     * @return DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * set feed description by xpath expression
     *
     * @param string $expression xpath expression
     */
    protected function setTime($expression)
    {
        $nodeList = $this->query($expression);
        if ($nodeList)
        {
            $this->time = new DateTime($nodeList->item(0)->nodeValue);
        }
    }


    /**
     * Get feed item text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * set feed description by xpath expression
     *
     * @param string $expression xpath expression
     */
    protected function setText($expression)
    {
        $nodeList = $this->query($expression);
        if ($nodeList)
        {
            $this->text = $nodeList->item(0)->nodeValue;
        }
    }

    /**
     * Get html formatted item text
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * set feed description by xpath expression
     *
     * @param string $expression xpath expression
     */
    protected function setHtml($expression)
    {
        $nodeList = $this->query($expression);
        if ($nodeList)
        {
            $this->html = $nodeList->item(0)->nodeValue;
        }
    }

    /**
     * Get feed item image url.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }


   /**
     * set feed description by xpath expression
     *
     * @param string $expression xpath expression
     */
    protected function setImage($expression)
    {
        $nodeList = $this->query($expression);
        if ($nodeList)
        {
            $this->image = $nodeList->item(0)->nodeValue;
        }
    }

    /**
     * query current doc iwth xpath expression
     *
     * @see BaseFeedParser::query()
     *
     * @param string $expression
     * @param DOMNode $contextNode
     *
     * @return DOMNodeList or FALSE if no items found
     */
    protected function query($expression)
    {
        return $this->feed->query($expression, $this->contextnode);
    }


}