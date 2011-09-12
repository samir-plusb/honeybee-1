<?php

class AssetService implements IAssetService
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
    public function put($assetUri, array $metaData = array())
    {
        
    }
    
    /**
     * Update the metadata of the asset with the given $assetId
     *
     * @param       int $assetId
     * @param       array $metaData
     *   
     * @return      IAssetInfo
     */
    public function update($assetId, array $metaData = array())
    {
        
    }
    
    /**
     * Retrieves the corresponding IAssetInfo instance for a given $assetId.
     * 
     * @return      IAssetInfo
     */
    public function get($assetId)
    {
        
    }
    
    /**
     * Deletes the IAssetInfo and it's corresponding binary for th given $assetId
     * 
     * @return      IAssetInfo
     */
    public function delete($assetId)
    {
        
    }
}

?>