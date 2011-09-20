<?php

/**
 * The BaseFeedParser is a concrete implementation of the IFeedParser interface
 * and provides basic functionality that usefull to all concrete IFeedParser implementations.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Parser
 */
abstract class BaseFeedParser implements IFeedParser
{
    /**
     * XML Document of current feed
     *
     * @var DOMDocument
     */
    protected $doc;

    /**
     * XML XPath of current feed document
     *
     * @var DOMXPath
     */
    protected $xpath;

    /**
     * indikates parser state
     * @var boolean
     */
    private $parsed;

    /**
     *
     * feed title
     * @var string
     */
    protected $title;

    /**
     *
     * feed description or subtitle
     * @var string
     */
    protected $description;

    /**
     * feed link or URL
     * @var string
     */
    protected $link;

    /**
     * feed timestamp
     * @var DateTime
     */
    protected $time;

    /**
     * feed rights
     * @var string
     */
    protected $copyright;

    /**
     * list of feed items
     * @var array
     */
    protected $items;

    /**
     * (non-PHPdoc)
     * @see IFeedParser::getTitle()
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string xpath expression
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
     * (non-PHPdoc)
     * @see IFeedParser::getLink()
     */
    public function getLink()
    {
        return $this->link;
    }


    /**
     * set feed url by xpath expression
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
     * (non-PHPdoc)
     * @see IFeedParser::getCopyright()
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * set feed rights by xpath expression
     *
     * @param string $expression xpath expression
     */
    protected function setCopyright($expression)
    {
        $nodeList = $this->query($expression);
        if ($nodeList)
        {
            $this->copyright = $nodeList->item(0)->nodeValue;
        }
    }

    /**
     * (non-PHPdoc)
     * @see IFeedParser::getDescription()
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * set feed description by xpath expression
     *
     * @param string $expression xpath expression
     */
    protected function setDescription($expression)
    {
        $nodeList = $this->query($expression);
        if ($nodeList)
        {
            $this->description = $nodeList->item(0)->nodeValue;
        }
    }

    /**
     * (non-PHPdoc)
     * @see IFeedParser::getTime()
     */
    public function getTime()
    {
        return $this->time ? $this->time : new DateTime();
    }


    /**
     * set feed description by xpath expression
     *
     * @param string $expression xpath expression
     */
    protected function setTime($expression)
    {
        $nodeList = $this->query($expression);
        $this->time = new DateTime($nodeList ? $nodeList->item(0)->nodeValue : NULL);
    }

    /**
     * (non-PHPdoc)
     * @see IFeedParser::getItems()
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * set items collection
     *
     * @param array $items
     */
    public function addItem(BaseFeedItem $item)
    {
        $this->items[] = $item;
    }


    /**
     * initialize a feed item data array
     *
     * <ul>
     * <li>author - string
     * <li>title - string
     * <li>link - string
     * <li>time - Item - DateTime
     * <li>text - string
     * <li>html - string
     * </ul>
     *
     * @return      array
     */
    protected function initItemData()
    {
        return array(
            'author' => '',
            'title' => '',
            'link' => '',
            'time' => NULL,
            'text' => '',
            'html' => ''
            );
    }


    /**
     * query current doc iwth xpath expression
     *
     * @param string $expression
     * @param DOMNode $contextnode
     *
     * @return DOMNodeList or FALSE if no items found
     */
    public function query($expression, DOMNode $contextnode = NULL)
    {
        libxml_clear_errors();
        $nl = $this->xpath->query($expression, $contextnode);
        $err = libxml_get_last_error();
        if ($err)
        {
            error_log(__METHOD__."($expression) ".print_r($err,1));
        }
        return ($nl && $nl->length > 0) ? $nl : FALSE;
    }

    /**
     * return parser state
     *
     * @return boolean
     */
    final protected function isParseable()
    {
        return ! $this->parsed;
    }

    /**
     * Setup feed parsing
     *
     * @param DomDocument $doc
     */
    public function __construct(DomDocument $doc)
    {
        $this->doc = $doc;
        $this->xpath = new DOMXPath($doc);
        $this->items = array();
    }


    public function __sleep()
    {
        $this->parsed = true;
        return array(
            'title',
            'link',
            'time',
            'description',
            'copyright',
            'items',
            'parsed'
        );
    }

    public function __wakeup()
    {
        $this->doc = NULL;
        $this->xpath = NULL;
    }

    // ---------------------------------- </BASE WORKING METHODS> --------------------------------


}

?>