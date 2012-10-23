<?php

/**
 * The RssDataRecord class is a concrete implementation of the NewsDataRecord base class.
 * It provides handling for rss item data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <tom.anheyer@berlinonline.de>
 * @package         News
 * @subpackage      Import/Rss
 */
class RssDataRecord extends NewsDataRecord
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of our author property.
     */
    const PROP_AUTHOR = 'author';

    const PROP_LINK = 'link';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    protected $author;

    protected $link;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    /**
     * Returns our author.
     *
     * @return      string
     */
    public function getAuthor()
    {
        return $this->author;
    }


    public function getLink()
    {
        return $this->link;
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------


    // ---------------------------------- <NewsDataRecord OVERRIDES> -----------------------

    /**
     * Return an array holding property names of properties,
     * which we want to expose through our IDataRecord::toArray() method.
     *
     * @return      array
     */
    public function getExposedProperties()
    {
        return array_merge(
            parent::getExposedProperties(),
            array(
                self::PROP_AUTHOR,
                self::PROP_LINK
            )
        );
    }

    // ---------------------------------- </NewsDataRecord OVERRIDES> ----------------------


    // ---------------------------------- <NewsDataRecord IMPL> ----------------------------

    /**
     * Parse the incoming feed item data
     *
     * @param       BaseFeedItem $data
     *
     * @return      array
     *
     * @see         NewsDataRecord::parse()
     */
    protected function parseData($data)
    {
        $html = trim($data->getHtml());
        $content = $html;
        if (empty($html))
        {
            $content = htmlspecialchars($data->getText());
        }

        return array(
            self::PROP_IDENT => $data->getIdentifier(),
            self::PROP_TITLE => $data->getTitle(),
            self::PROP_TIMESTAMP => $data->getTime(),
            self::PROP_CONTENT => $content,
            self::PROP_MEDIA => $this->importMedia($data),
            self::PROP_GEO => array(),
            self::PROP_AUTHOR => $data->getAuthor(),
            self::PROP_LINK => $data->getLink()
        );
    }

    protected function importMedia(BaseFeedItem $item)
    {
        $media = array();
        if (($assetUri = $item->getImage()))
        {
            $meta = array(
                'caption' => $item->getText(),
                'title'   => $item->getTitle()
            );
            $asset = ProjectAssetService::getInstance()->findByOrigin($assetUri);
            if (! $asset)
            {
                $asset = ProjectAssetService::getInstance()->put($assetUri, $meta);
            }
            $media[] = $asset->getIdentifier();
        }
        return $media;
    }

    // ---------------------------------- </NewsDataRecord IMPL> ---------------------------
}

?>