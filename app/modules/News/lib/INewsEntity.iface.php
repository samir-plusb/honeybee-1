<?php

/**
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         News
 */
interface INewsEntity
{
    /**
     * Returns our title.
     *
     * @return      string
     */
    public function getTitle();

    /**
     * Returns our content.
     *
     * @return      string
     */
    public function getContent();

    /**
     * Returns our category.
     *
     * @return      string
     */
    public function getCategory();

    /**
     * Returns our media (image, video and file assets for example).
     * The returned value is an array holding id's that can be used together with our ProjectAssetService
     * implementations.
     * Example return value structure:
     * -> return array(23, 24, 512, 13);
     *
     * @return      array
     */
    public function getMedia();

    /**
     * Returns our geo data in the following structure:
     * -> return array(
     *        'long' => $longValue,
     *        'lat'  => $latValue
     *    );
     *
     * @return      array
     */
    public function getGeoData();
}

?>
