<?php
/**
 * The BaseFeedItem class defines a base implementation of the feeditem concept
 * and also allready implements most of it.
 * It serves as the base class to all feed items created on the project.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <tom.anheyer@berlinonline.de>
 * @package         Lib
 * @subpackage      Parser
 */
abstract class BaseFeedItem
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds our identifier.
     *
     * @var         string
     */
    protected  $id;

    /**
     * Holds our author.
     *
     * @var         string
     */
    protected $author;

    /**
     * Holds our title.
     *
     * @var         string
     */
    protected $title;

    /**
     * Holds our link.
     *
     * @var         string
     */
    protected $link;

    /**
     * Holds our time.
     *
     * @var         DateTime
     */
    protected $time;

    /**
     * Holds our text content.
     *
     * @var         string
     */
    protected $text;

    /**
     * Holds our html content.
     *
     * @var         string
     */
    protected $html;

    /**
     * Holds our image uri.
     *
     * @var         string
     */
    protected $image;

    /**
     * Holds our feed parser instance.
     *
     * @var         BaseFeedParser
     */
    protected $feed;

    /**
     * Holds a context node used for xpath queries.
     *
     * @var         DOMNode
     */
    protected $contextnode;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------

    /**
     * Create a new BaseFeedItem instance, thereby providing an IFeedParser impl as our "parent",
     * which means this is the parser that is creating us and a context node used for relative xpath queries.
     *
     * @param       BaseFeedParser $feed
     * @param       DOMNode $contextnode
     */
    public function __construct(BaseFeedParser $feed, DOMNode $contextnode)
    {
        $this->feed = $feed;
        $this->contextnode = $contextnode;
        $this->parseItem();
    }

    // ---------------------------------- </CONSTRUCTOR> -----------------------------------------


    // ---------------------------------- <ABSTRACT METHODS> -------------------------------------

    /**
     * Parse xml node describing an item into our instance members.
     */
    abstract protected function parseItem();

    // ---------------------------------- </ABSTRACT METHODS> ------------------------------------

    public function getId()
    {
        return empty($this->id) ? $this->getLink() : $this->id;
    }

    /**
     * set feed description by xpath expression
     *
     * @param       string $expression xpath expression
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
     * @return      string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * set feed description by xpath expression.
     *
     * @param       string $expression xpath expression
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
     * Get feed item title.
     *
     * @return      string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * set feed description by xpath expression.
     *
     * @param       string $expression xpath expression
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
     * @return      string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * set feed description by xpath expression
     *
     * @param       string $expression xpath expression
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
     * Get feed item timestamp.
     *
     * @return      DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set feed description by xpath expression.
     *
     * @param       string $expression xpath expression
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
     * Get feed item text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set feed description by xpath expression.
     *
     * @param       string $expression xpath expression
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


    /**
     * return serializable member variables
     *
     * @return      array
     */
    public function __sleep()
    {
        return array(
            'id',
            'author',
            'title',
            'link',
            'time',
            'text',
            'html',
            'image'
        );
    }
}

?>