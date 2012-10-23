<?php

/**
 * The IAssetService interface defines the public api that all asset services must provide.
 * IAssetServices are responsebale for storing and retrieving files and related meta-data
 * based on uris and ids., whereas uris are used to provide and ids are used to retrieve assets.
 * IAssetService implementations must also deal with IAssetInfo interface as this is defined
 * exchange datatype for IAssetService methods.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Service
 */
interface IAssetService
{
    /**
     * Store the given file on the filesystem
     * and returns a IAssetInfo instance that reflects our new asset.
     *
     * @param       string $assetUri
     * @param       array $metaData
     *
     * @return      IAssetInfo
     */
    public function put($assetUri, array $metaData = array());

    /**
     * Update the metadata of the asset with the given $assetId
     *
     * @param       int $assetId
     * @param       array $metaData
     *
     * @return      IAssetInfo
     */
    public function update(IAssetInfo $asset, array $metaData = array());

    /**
     * Retrieves the corresponding IAssetInfo instance for a given $assetId.
     *
     * @return      IAssetInfo
     */
    public function get($assetId);

    /**
     * Retrieves the corresponding IAssetInfo instance for a given $origin.
     *
     * @return      IAssetInfo
     */
    public function findByOrigin($origin);

    /**
     * Deletes the IAssetInfo and it's corresponding binary for th given $assetId
     *
     * @return      IAssetInfo
     */
    public function delete($assetId);
}

?>