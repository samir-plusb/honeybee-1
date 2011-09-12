<?php

class ProjectAssetService implements IAssetService
{
    const ASSET_PATH_DEPTH = 4;
    
    const ASSET_FOLDER2ID_OFFSET = 3;
    
    const URI_PART_SCHEME = 'scheme';
    
    const URI_PART_PATH = 'path';
    
    const URI_SCHEME_FILE = 'file';
    
    const URI_SCHEME_HTTP = 'http';
    
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
        $localPath = $this->convertUriToLocalPath($assetUri);
        
        
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
    
    protected function convertUriToLocalPath($assetUri)
    {
        $uriParts = parse_url($assetUri);
        
        if (!isset($uriParts[self::URI_PART_SCHEME]) || empty($uriParts[self::URI_PART_SCHEME]))
        {
            $uriParts[self::URI_PART_SCHEME] = self::URI_SCHEME_FILE;
        }
        
        $filePath = null;
        
        if (self::URI_SCHEME_FILE !== $uriParts[self::URI_PART_SCHEME])
        {
            $filePath = $this->downloadAsset($assetUri);
        }
        else
        {
            $filePath = $uriParts[self::URI_PART_PATH];
        }
        
        return $filePath;
    }
    
    protected function downloadAsset($assetUri)
    {
        $downloasFilePath = '';
        
        // @todo download the asset.
        
        return $downloasFilePath;
    }
    
    protected function generateTargetPath($assetId)
    {
        
    }
}

?>