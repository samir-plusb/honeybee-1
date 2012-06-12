<?php

/**
 * The BaseFeedParser is a concrete implementation of the IFeedParser interface
 * and provides basic functionality that usefull to all concrete IFeedParser implementations.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <tom.anheyer@berlinonline.de>
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Parser/Feed
 */
abstract class BaseFeedParser implements IFeedParser
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds the XML Document of current feed.
     *
     * @var         DOMDocument
     */
    protected $doc;

    /**
     * Holds the XML XPath of current feed document.
     *
     * @var         DOMXPath
     */
    protected $xpath;

    /**
     * Indicates parser the parser's state (parsed | not parsed).
     *
     * @var         boolean
     */
    private $parsed;

    /**
     * Holds our feed's title.
     *
     * @var         string
     */
    protected $title;

    /**
     * Holds our feed's description or subtitle.
     *
     * @var         string
     */
    protected $description;

    /**
     * Holds our feed's link or URL.
     *
     * @var         string
     */
    protected $link;

    /**
     * Holds the feed's timestamp.
     *
     * @var         DateTime
     */
    protected $time;

    /**
     * Holds the feed's copyright.
     *
     * @var         string
     */
    protected $copyright;

    /**
     * Holds a list of feed items.
     *
     * @var         array
     */
    protected $items;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------

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

    // ---------------------------------- </CONSTRUCTOR> -----------------------------------------


    // ---------------------------------- <IFeedParser IMPL> -------------------------------------

    /**
     * Returns our feed title.
     *
     * @see         IFeedParser::getTitle()
     *
     * @return      string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets our feed title.
     *
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
     * Returns our feed link.
     *
     * @see         IFeedParser::getLink()
     *
     * @return      string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Sets our feed link to the value resulting from
     * evaluating the given $expression on our $xpath member..
     *
     * @param string xpath expression
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
     * Returns our feed copyright.
     *
     * @see         IFeedParser::getCopyright()
     *
     * @return      string
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * Sets our feed copyright to the value resulting from
     * evaluating the given $expression on our $xpath member.
     *
     * @param string xpath expression
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
     * Returns our feed description.
     *
     * @see         IFeedParser::getDescription()
     *
     * @return      string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets our feed description to the value resulting from
     * evaluating the given $expression on our $xpath member.
     *
     * @param string xpath expression
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
     * Returns our feed timestamp.
     *
     * @see         IFeedParser::getTime()
     *
     * @return      DateTime
     */
    public function getTime()
    {
        return $this->time ? $this->time : new DateTime();
    }

    /**
     * Sets our feed timestamp to a DateTime created from the value,
     * that is returned from our $expression evaluation.
     *
     * @param string xpath expression
     */
    protected function setTime($expression)
    {
        $nodeList = $this->query($expression);
        $this->time = new DateTime($nodeList ? $nodeList->item(0)->nodeValue : NULL);
    }

    /**
     * Returns our feed items.
     *
     * @see         IFeedParser::getItems()
     *
     * @return      array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Add an item to our feed items list.
     *
     * @param       BaseFeedItem $item
     */
    public function addItem(BaseFeedItem $item)
    {
        $this->items[] = $item;
    }

    // ---------------------------------- </IFeedParser IMPL> ------------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Query current doc with xpath expression.
     *
     * @param       string $expression
     * @param       DOMNode $contextnode
     *
     * @return      DOMNodeList or FALSE if no items found
     *
     * @todo        Is this really a public method? Maybe make it protected?
     */
    public function query($expression, DOMNode $contextnode = NULL)
    {
        libxml_clear_errors();
        $nodeList = $this->xpath->query($expression, $contextnode);
        $err = libxml_get_last_error();

        if ($err)
        {
            error_log(__METHOD__."($expression) ".print_r($err,1));
        }

        return ($nodeList && $nodeList->length > 0) ? $nodeList : FALSE;
    }

    /**
     * Return our parser state.
     *
     * @return      boolean
     */
    final protected function isParseable()
    {
        return ! $this->parsed;
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------


    // ---------------------------------- <PHP SERIALIZE CALLBACKS> ------------------------------

    /**
     * Returns an array of property names,
     * oof properties that shall be included into serialization.
     *
     * @return      array
     */
    public function __sleep()
    {
        $this->parsed = TRUE;

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

    /**
     * Does stuff to restore our state after we have been deserialized.
     */
    public function __wakeup()
    {
        $this->doc = NULL;
        $this->xpath = NULL;
    }

    // ---------------------------------- </PHP SERIALIZE CALLBACKS> -----------------------------
}

?>