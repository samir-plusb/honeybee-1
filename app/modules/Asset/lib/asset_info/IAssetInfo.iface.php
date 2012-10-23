<?php

/**
 * The IAssetInfo interface defines an api for exposing file based information
 * extended by any kind of optional metadata.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      AssetInfo
 */
interface IAssetInfo
{
    /**
     * Returns our origin.
     *
     * @return      string
     */
    public function getOrigin();

    /**
     * Return the asset's full filename,
     * means name+extension
     *
     * @return      string
     */
    public function getFullName();

    /**
     * Returns the asset's filename,
     * without the extension.
     *
     * @return      string
     */
    public function getName();

    /**
     * Returns the asset's file extension.
     *
     * @return      string
     */
    public function getExtension();

    /**
     * Returns an absolute filesystem path,
     * pointing to the asset's binary.
     *
     * @return      string
     */
    public function getFullPath();

    /**
     * Returns the size of the asset's binary file on the filesystem.
     *
     * @return      int
     */
    public function getSize();

    /**
     * Return the mime-type of our assets file.
     */
    public function getMimeType();

    /**
     * Return an array containing additional meta for our asset.
     *
     * @return      array
     */
    public function getMetaData();
}

?>