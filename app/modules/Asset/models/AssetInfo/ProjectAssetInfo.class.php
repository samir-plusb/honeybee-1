<?php

class ProjectAssetInfo implements IAssetInfo
{
    const ASSET_MAX_ID = 100000000;
                         
    const ASSET_FOLDER_SIZE = 512;
    
    const PROP_ID = 'id';
    
    const XPROP_FULLNAME = 'fullname';
    
    const XPROP_NAME = 'name';
    
    const XPROP_EXTENSION = 'extension';
    
    const XPROP_SIZE = 'size';
    
    const XPROP_ORIGIN = 'origin';
    
    const XPROP_MIME_TYPE = 'mimeType';
    
    const XPROP_META_DATA = 'metaData';
    
    const GETTER_METHOD_PREFIX = 'get';
    
    const SETTER_METHOD_PREFIX = 'set';
    
    protected $id;
    
    protected $origin;
    
    protected $fullPath;
    
    protected $fullName;
    
    protected $name;
    
    protected $extension;
    
    protected $size = 0;
    
    protected $mimeType = '';
    
    protected $metaData = array();
    
    public function __construct($assetId, array $assetData = array())
    {
        $this->id = $assetId;
        
        if (!empty($assetData))
        {
            $this->hydrate($assetData);
        }
        
        $this->fullPath = $this->generateAbsTargetPath($this->id);
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getOrigin()
    {
        return $this->origin;
    }
    
    protected function setOrigin($origin)
    {
        $this->origin = $origin;
        
        $originParts = parse_url($origin);
        $explodedOrigin = explode('/', $originParts['path']);
        $this->fullName = end($explodedOrigin);
        
        $explodedName = explode('.', $this->fullName);
        $extension = array_pop($explodedName);
        $this->name = implode('.', $explodedName);
        
        if ($extension)
        {
            $this->extension = strtolower($extension);
        }
    }
    
    public function getExtension()
    {
        return $this->extension;
    }
    
    public function getFullName()
    {
        return $this->fullName;
    }
    
    public function getMetaData()
    {
        return $this->metaData;
    }
    
    protected function setMetaData($metaData)
    {
        $this->metaData = (array)$metaData;
    }
    
    public function getMimeType()
    {
        return $this->mimeType;
    }
    
    protected function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getFullPath()
    {
        return $this->fullPath;
    }
    
    public function getSize()
    {
        return $this->size;
    }
    
    protected function setSize($size)
    {
        $this->size = $size;
    }
    
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
    }
    
    public function moveFile($moveOrigin = TRUE)
    {
        $originParts = parse_url($this->getOrigin());
        $src = null;
        
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
            $fInfo = new SplFileInfo($targetPath);
            $this->mimeType = $fInfo->getType();
            $this->size = $fInfo->getSize();
            
            return TRUE;
        }
        
        return FALSE;
    }
    
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
    
    public function fileExists()
    {
        return file_exists($this->getFullPath());
    }
    
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
    
    protected function getExposedPropNames()
    {
        return array(
            self::PROP_ID,
            self::XPROP_ORIGIN,
            self::XPROP_FULLNAME,
            self::XPROP_NAME,
            self::XPROP_EXTENSION,
            self::XPROP_SIZE,
            self::XPROP_MIME_TYPE,
            self::XPROP_META_DATA
        );
    }
    
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
    
    protected function fetchAsset()
    {
        $curlHandle = ProjectCurl::create();
        
        $tempPath = $this->getDowloadTmpPath();
        
        curl_setopt($curlHandle, CURLOPT_URL, $this->getOrigin());
        $rawData = curl_exec($curlHandle);
        
        $error = curl_error($curlHandle);
        $errorNum = curl_errno($curlHandle);
        $respCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        
        if (200 > $respCode || 300 <= $respCode || $errorNum || $error)
        {
            throw new Exception(
                "Failed to download asset binary from uri: " . $this->getOrigin()
            );
        }
        
        file_put_contents($tempPath, $rawData);
        
        return $tempPath;
    }
    
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
}

?>