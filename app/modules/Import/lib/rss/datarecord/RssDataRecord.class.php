<?php

/**
 * The RssDataRecord class is a concrete implementation of the ImportBaseDataRecord base class.
 * It provides handling for rss item data.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <tom.anheyer@berlinonline.de>
 * @package         Import
 * @subpackage      Imperia
 */
class RssDataRecord extends ImportBaseDataRecord
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of our author property.
     */
    const PROP_AUTHOR = 'author';
    const PROP_LINK = 'link';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds our rss item data.
     *
     * @var BaseFeedItem
     */
    protected $data;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    /**
     * Returns our author.
     *
     * @return      string
     */
    public function getAuthor()
    {
        return $this->data->getAuthor();
    }


    public function getLink()
    {
        return $this->data->getLink();
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------


    // ---------------------------------- <ImportBaseDataRecord OVERRIDES> -----------------------

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

    // ---------------------------------- </ImportBaseDataRecord OVERRIDES> ----------------------


    // ---------------------------------- <ImportBaseDataRecord IMPL> ----------------------------

    /**
     * Parse the incoming feed item data
     *
     * @param       BaseFeedItem $data
     *
     * @return      array
     *
     * @see         ImportBaseDataRecord::parse()
     */
    protected function parseData($data)
    {
        $this->data = $data;

        $media = array();

        if (($assetUri = $data->getImage()))
        {
            $meta = array(
                'caption' => $data->getText(),
                'title'   => $data->getTitle()
            );

            $asset = ProjectAssetService::getInstance()->findByOrigin($assetUri);

            if (! $asset)
            {
                $asset = ProjectAssetService::getInstance()->put($assetUri, $meta);
            }

            $media[] = $asset->getId();
        }

        $html = trim($data->getHtml());
        $content = $html;
        if (empty($html))
        {
            $content = htmlspecialchars($data->getText());
        }

        return array(
            self::PROP_IDENT => $data->getId(),
            self::PROP_TITLE => $data->getTitle(),
            self::PROP_TIMESTAMP => $data->getTime(),
            self::PROP_CONTENT => $content,
            self::PROP_MEDIA => $media,
            self::PROP_GEO => array()
        );
    }

    // ---------------------------------- </ImportBaseDataRecord IMPL> ---------------------------
}

?>