<?php

/**
 * The ProjectAssetInfo is a concrete implementation of the IAssetInfo interface.
 * It reflects asset based imformation and provides an interface for moving and deleting
 * asset files from the underlying filesystem.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      AssetInfo
 */
class ProjectAssetInfo implements IAssetInfo
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the max number of files per directory.
     */
    const ASSET_FOLDER_SIZE = 512;

    /**
     * Holds the name of our id property.
     */
    const PROP_ASSET_ID = 'id';

    /**
     * Holds the name of our fullname property.
     */
    const XPROP_FULLNAME = 'fullname';

    /**
     * Holds the name of our name property.
     */
    const XPROP_NAME = 'name';

    /**
     * Holds the name of our extension property.
     */
    const XPROP_EXTENSION = 'extension';

    /**
     * Holds the name of our size property.
     */
    const XPROP_SIZE = 'size';

    /**
     * Holds the name of our origin property.
     */
    const XPROP_ORIGIN = 'origin';

    /**
     * Holds the name of our mimeType property.
     */
    const XPROP_MIME_TYPE = 'mimeType';

    /**
     * Holds the name of our metaData property.
     */
    const XPROP_META_DATA = 'metaData';

    /**
     * Holds the property prefix we use to build getter method names.
     */
    const GETTER_METHOD_PREFIX = 'get';

    /**
     * Holds the property prefix we use to build setter method names.
     */
    const SETTER_METHOD_PREFIX = 'set';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * The asset's id (also couchdb _id).
     *
     * @var         int
     */
    protected $assetId;

    /**
     * The asset's origin.
     *
     * @var         string
     */
    protected $origin;

    /**
     * The asset's absolute fs path.
     *
     * @var         string
     */
    protected $fullPath;

    /**
     * The asset's full name (including extension)
     *
     * @var         string
     */
    protected $fullname;

    /**
     * The asset's bare name (excluding extension)
     *
     * @var         string
     */
    protected $name;

    /**
     * The asset's extension.
     *
     * @var         string
     */
    protected $extension;

    /**
     * The size of the asset's file.
     *
     * @var         int
     */
    protected $size = 0;

    /**
     * The asset's file's mime-type.
     *
     * @var         string
     */
    protected $mimeType = '';

    /**
     * Holds the asset's meta data.
     *
     * @var         array
     */
    protected $metaData = array();

    // ---------------------------------- <MEMBERS> ----------------------------------------------


    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------

    /**
     * Create a new ProjectAssetInfo instance from the given id and data,
     * that is hydrated if not empty.
     *
     * @param       int $assetId
     * @param       array $assetData
     */
    public function __construct($assetId, array $assetData = array())
    {
        $this->assetId = $assetId;

        if (!empty($assetData))
        {
            $this->hydrate($assetData);
        }

        $this->fullPath = $this->generateAbsTargetPath($this->assetId);

        $originParts = parse_url($this->getOrigin());

        if (!isset($originParts['scheme']))
        {
            throw new Exception("Invalid origin uri given: " . $this->getOrigin());
        }
    }

    // ---------------------------------- </CONSTRUCTOR> -----------------------------------------


    // ---------------------------------- <IAssetInfo IMPL> --------------------------------------

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
     * Return the uri that our asset orignates from.
     *
     * @return      string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Return the asset's full filename,
     * means name+extension
     *
     * @return      string
     */
    public function getFullName()
    {
        return $this->fullname;
    }

    /**
     * Returns the asset's filename,
     * without the extension.
     *
     * @return      string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the asset's file extension.
     *
     * @return      string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Returns an absolute filesystem path,
     * pointing to the asset's binary.
     *
     * @return      string
     */
    public function getFullPath()
    {
        return $this->fullPath;
    }

    /**
     * Returns the size of the asset's binary file on the filesystem.
     *
     * @return      int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Return the mime-type of our assets file.
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Return an array containing additional meta for our asset.
     *
     * @return      array
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * Return an array representation of this object.
     *
     * @return      array
     */
    public function toArray()
    {
        $data = array();

        foreach ($this->getExposedPropNames() as $property)
        {
            $getterMethod = self::GETTER_METHOD_PREFIX . ucfirst($property);
            $data[$property] = $this->$getterMethod();
        }

        return $data;
    }

    /**
     * Hydrate the given the data.
     *
     * @param       array $data
     */
    public function hydrate(array $data)
    {
        foreach ($this->getExposedPropNames() as $property)
        {
            $setterMethod = self::SETTER_METHOD_PREFIX . ucfirst($property);

            if (isset($data[$property]) && is_callable(array($this, $setterMethod)))
            {
                $this->$setterMethod($data[$property]);
            }
        }
        
        if (isset($data[self::XPROP_META_DATA]) && is_array($data[self::XPROP_META_DATA]))
        {
            $this->hydrate($data[self::XPROP_META_DATA]);
        }
        
        if (! $this->fullname)
        {
            $originParts = parse_url($this->getOrigin());
            $explodedOrigin = explode('/', $originParts['path']);
            $this->setFullname(end($explodedOrigin));
        }
    }

    /**
     * Move our binary to our target path on the filesystem.
     *
     * @return      boolean
     */
    public function moveFile($moveOrigin = TRUE)
    {
        $originParts = parse_url($this->getOrigin());
        $src = NULL;

        if ('file' === $originParts['scheme'])
        {
            $src = $originParts['path'];
        }
        else
        {
            $src = $this->fetchAsset();
        }

        $targetPath = $this->getFullPath();
        $dirname = dirname($targetPath);

        if (!file_exists($dirname))
        {
            if (!mkdir($dirname, 0755, TRUE))
            {
                throw new Exception("Failed creating target directory: " . $dirname);
            }
        }

        if (($moveOrigin && rename($src, $targetPath)) || copy($src, $targetPath))
        {
            // return mime type ala mimetype extension
            $finfo = new finfo(FILEINFO_MIME, AgaviConfig::get('assets.mime_database'));

            if (!$finfo) 
            {
                throw new  Exception("Opening fileinfo database failed");
            }
            
            if (! $this->mimeType)
            {
                $this->mimeType = $finfo->file($this->getFullPath());
            }
            
            $this->size = filesize($this->getFullPath());

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Delete our binary from our target path on the filesystem.
     *
     * @return      boolean
     */
    public function deleteFile()
    {
        $firstDir = dirname($this->getFullPath());
        $secondDir = dirname($firstDir);

        $return = unlink($this->getFullPath());

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
     * Move our binary to our target path on the filesystem.
     *
     * @return      boolean
     */
    public function fileExists()
    {
        return file_exists($this->getFullPath());
    }

    // ---------------------------------- </IAssetInfo IMPL> -------------------------------------



    // ---------------------------------- <HYDRATE SETTERS> --------------------------------------

    /**
     * Set our mime-type.
     *
     * @param       string $mimeType
     */
    protected function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * Set our mime-type.
     *
     * @param       string $mimeType
     */
    protected function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * Set our meta-data.
     *
     * @param       array $metaData
     */
    protected function setMetaData($metaData)
    {
        $this->metaData = (array)$metaData;
    }

    /**
     * Set our origin.
     *
     * @param       string $origin
     */
    protected function setOrigin($origin)
    {
        $this->origin = $origin;
    }
    
    protected function setFullname($name)
    {
        $this->fullname = $name;
        $explodedName = explode('.', $this->fullname);
        $extension = array_pop($explodedName);
        $this->name = implode('.', $explodedName);

        if ($extension)
        {
            $this->extension = strtolower($extension);
        }
    }

    // ---------------------------------- </HYDRATE SETTERS> -------------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Return array with properties that we want expose
     * in our toArray method and support for hydration.
     *
     * @return      array
     */
    protected function getExposedPropNames()
    {
        return array(
            self::PROP_ASSET_ID,
            self::XPROP_ORIGIN,
            self::XPROP_FULLNAME,
            self::XPROP_NAME,
            self::XPROP_EXTENSION,
            self::XPROP_SIZE,
            self::XPROP_MIME_TYPE,
            self::XPROP_META_DATA
        );
    }

    /**
     * Return a path relative to our asset base dir,
     * that points the location for the given id.
     *
     * @param       int $assetId
     *
     * @return      string
     */
    protected function generateRelTargetPath($assetId)
    {
        $first = $assetId % self::ASSET_FOLDER_SIZE;
        $second =  intval($assetId / self::ASSET_FOLDER_SIZE) % self::ASSET_FOLDER_SIZE;

        return sprintf(
            "%s%02x%s%02x%s",
            DIRECTORY_SEPARATOR,
            $first,
            DIRECTORY_SEPARATOR,
            $second,
            DIRECTORY_SEPARATOR . $assetId
        );
    }

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
     * Return an absolute fs path pointing to our asset location.
     *
     * @param       int $assetId
     *
     * @return      string
     */
    protected function generateAbsTargetPath($assetId)
    {
        $relPath = $this->generateRelTargetPath($assetId);

        $baseDir = realpath(AgaviConfig::get('assets.base_dir'));

        $extension = $this->getExtension();

        if ($extension)
        {
            $relPath .= '.' . $extension;
        }

        return $baseDir . $relPath;
    }

    /**
     * Download our asset from it's origin
     * to a temp path and return the latter.
     *
     * @throws      Exception on curl/file io errors
     * @return      string
     */
    protected function fetchAsset()
    {
        $curlHandle = ProjectCurl::create();

        $tempPath = $this->getDowloadTmpPath();
        
        $filePtr = fopen($tempPath,'wb');
        
        if (! $filePtr)
        {
            throw new Exception("Can not open file for writing: ".$tempPath);
        }

        curl_setopt($curlHandle, CURLOPT_URL, $this->getOrigin());
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 0);
        curl_setopt($curlHandle, CURLOPT_FILE, $filePtr);
        curl_exec($curlHandle);

        $error = curl_error($curlHandle);
        $errorNum = curl_errno($curlHandle);
        $respCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        fclose($filePtr);

        if (200 > $respCode || 300 <= $respCode || $errorNum || $error)
        {
            throw new Exception(
                "Failed to download asset binary from uri: " . $this->getOrigin()
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