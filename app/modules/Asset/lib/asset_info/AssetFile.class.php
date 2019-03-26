<?php

use Guzzle\Http\Client;
use Guzzle\Common\Event;
use Guzzle\Http\Message\Response;

/**
 * The AssetFile is a concrete implementation of the IAssetFile interface.
 * It exposes a coarse grained api for moving and deleting files.
 * 
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      AssetInfo
 */
class AssetFile implements IAssetFile
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the max number of files per directory.
     */
    const ASSET_FOLDER_SIZE = 512;
    
    // ---------------------------------- </CONSTANTS> -------------------------------------------
    
    
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds our asset id.
     * 
     * @var         int
     */
    protected $assetId;
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------
    
    /**
     * Create a new AssetFile instance.
     * 
     * @param       int $assetId 
     */
    public function __construct($assetId)
    {
        $this->assetId = $assetId;
    }
    
    // ---------------------------------- </CONSTRUCTOR> -----------------------------------------
    
    
    // ---------------------------------- <IAssetFile IMPL> --------------------------------------
    
    /**
     * Returns our unique identifier.
     * 
     * @return      int
     */
    public function getId()
    {
        return $this->assetId;
    }
    
    /**
     * Move our binary to our target path on the filesystem.
     *
     * @return      boolean
     */
    public function move($assetUri, $moveOrigin = TRUE)
    {
        $sourcePath = $this->resolveAssetUri($assetUri);
        $targetPath = $this->getPath();
        $dirname = dirname($targetPath);
        if (! file_exists($dirname))
        {
            if (!mkdir($dirname, 0755, TRUE))
            {
                throw new Exception("Failed creating target directory: " . $dirname);
            }
        }
        
        return (($moveOrigin && rename($sourcePath, $targetPath)) || copy($sourcePath, $targetPath));
    }

    /**
     * Delete our binary from our target path on the filesystem.
     *
     * @return      boolean
     */
    public function delete()
    {
        $filePath = $this->getPath();
        $firstDir = dirname($filePath);
        $secondDir = dirname($firstDir);
        $return = unlink($filePath);

        if (!$this->hasFiles($firstDir))
        {
            rmdir($firstDir);
        }
        if (!$this->hasFiles($secondDir))
        {
            rmdir($secondDir);
        }
        return $return;
    }
    
    /**
     * Return an absolute fs path pointing to our asset location.
     *
     * @return      string
     */
    public function getPath()
    {
        $first = $this->assetId % self::ASSET_FOLDER_SIZE;
        $second =  intval($this->assetId / self::ASSET_FOLDER_SIZE) % self::ASSET_FOLDER_SIZE;
        $relPath = sprintf(
            "%s%02x%s%02x%s",
            DIRECTORY_SEPARATOR,
            $first,
            DIRECTORY_SEPARATOR,
            $second,
            DIRECTORY_SEPARATOR . $this->assetId
        );

        $baseDir = realpath(AgaviConfig::get('assets.base_dir'));
        return $baseDir . $relPath;
    }
    
    /**
     * Tells you if our asset in present on the file system.
     *
     * @return      boolean
     */
    public function fileExists()
    {
        return file_exists($this->getPath());
    }
    
    // ---------------------------------- </IAssetFile IMPL> -------------------------------------
    
    
    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    /**
     * Return true if the given directory is empty,
     * false otherwise.
     *
     * @param       string $directory
     *
     * @return      boolean
     */
    protected function hasFiles($directory)
    {
        $dirHandle = opendir($directory);
        if (!$directory)
        {
            throw new Exception(
                "Unable to open directory handle for dir: " . $directory
            );
        }

        $ignoredFiles = array('.', '..');
        while (FALSE !== ($file = readdir($dirHandle)))
        {
            if (!in_array($file, $ignoredFiles))
            {
                closedir($dirHandle);
                return TRUE;
            }
        }
        return FALSE;
    }
    
    /**
     * Resolve the given uri to a local filepath,
     * thereby previously downloading extrinsic files to the local drive,
     * if neccessary.
     * 
     * @param       string $assetUri
     * 
     * @return      string
     */
    protected function resolveAssetUri($assetUri)
    {
        $uriParts = parse_url($assetUri);
        if (!isset($uriParts['scheme']))
        {
            throw new Exception("Invalid origin uri given: " . $assetUri);
        }
        
        $src = NULL;
        if ('file' === $uriParts['scheme'])
        {
            $src = $uriParts['path'];
        }
        else
        {
            $src = $this->downloadAsset($assetUri);
        }
        return $src;
    }
    
    /**
     * Download our asset from it's origin
     * to a temp path and return the latter.
     *
     * @throws      Exception on curl/file io errors
     * @return      string
     */
    protected function downloadAsset($assetUri)
    {
        $tempPath = $this->getDowloadTmpPath();
        $filePtr = fopen($tempPath,'wb');

        if (! $filePtr)
        {
            throw new Exception("Can not open file for writing: ".$tempPath);
        }

        $client = new Client($assetUri);

	$client->getEventDispatcher()->addListener('request.error', function(Event $event)
        {
            $newResponse = new Response($event['response']->getStatusCode());
            $event['response'] = $newResponse;
            $event->stopPropagation();
        });

        $request = $client->get(NULL, NULL, $filePtr);
        $response = $request->send();

        if (200 > $response->getStatusCode() || 300 <= $response->getStatusCode())
        {
            throw new Exception(
                "Failed to download asset binary from uri: $assetUri' resp code: $respCode ($error)"
            );
        }

        return $tempPath;
    }

    /**
     * Build a temp path that we can safely download asset files to,
     * before importing them.
     *
     * @return      string
     */
    protected function getDowloadTmpPath()
    {
        $baseDir = AgaviConfig::get('assets.base_dir');
        $tmpDir = realpath($baseDir) . DIRECTORY_SEPARATOR .
            'tmp' . DIRECTORY_SEPARATOR .
            'download' . DIRECTORY_SEPARATOR;

        if (!is_dir($tmpDir))
        {
            if (!mkdir($tmpDir, 0775, TRUE))
            {
                throw new Exception("Failed to create temp download path: " . $tmpDir);
            }
        }
        return tempnam($tmpDir, 'dwn_');
    }
    
    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>