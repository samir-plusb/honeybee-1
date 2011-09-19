<?php

/**
 * The IAssetFile interface defines a base api for handling an asset's filesystem logic.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      AssetInfo
 */
interface IAssetFile
{
    /**
     * Returns our unique identifier.
     * 
     * @return      int
     */
    public function getId();
    
    /**
     * Move our binary to our target path on the filesystem.
     * 
     * @return      boolean
     */
    public function move($assetUri, $moveOrigin = TRUE);
    
    /**
     * Delete our binary from our target path on the filesystem.
     * 
     * @return      boolean
     */
    public function delete();
    
    /**
     * Return an absolute fs path pointing to our asset location.
     *
     * @return      string
     */
    public function getPath();
    
    /**
     * Move our binary to our target path on the filesystem.
     * 
     * @return      boolean
     */
    public function fileExists();
}

?>