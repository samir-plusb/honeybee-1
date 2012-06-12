<?php

/**
 * The ProjectAssetInfo is a concrete implementation of the IAssetInfo interface.
 * It reflects asset based imformation and provides an interface for moving and deleting
 * asset files from the underlying filesystem.
 *
 * @version         $Id: ProjectAssetInfo.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      AssetInfo
 */
class ProjectAssetInfo extends BaseDocument implements IAssetInfo
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    protected $revision;

    /**
     * The asset's origin.
     *
     * @var         string
     */
    protected $origin;

    /**
     * The asset's full name (including extension)
     *
     * @var         string
     */
    protected $fullName;

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

    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    // ---------------------------------- <IAssetInfo IMPL> --------------------------------------

    public function getRevision()
    {
        return $this->revision;
    }

    public function setRevision($revision)
    {
        $this->revision = $revision;
    }

    /**
     * Return the asset's origin.
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
        return $this->fullName;
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
        if ($this->identifier)
        {
            $file = new AssetFile($this->identifier);
            return $file->getPath();
        }
        return NULL;
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

    public function setMetaData(array $metaData)
    {
        $this->metaData = $metaData;
    }

    // ---------------------------------- </IAssetInfo IMPL> -------------------------------------


    // ---------------------------------- <HYDRATE SETTERS> --------------------------------------

    /**
     * Sets our fullname.
     *
     * @param       string $name
     */
    protected function setFullname($fullName)
    {
        $this->fullName = $fullName;
        $explodedName = explode('.', $this->fullName);
        $extension = array_pop($explodedName);
        $this->name = implode('.', $explodedName);
        if ($extension)
        {
            $this->extension = strtolower($extension);
        }
    }

    // ---------------------------------- </HYDRATE SETTERS> -------------------------------------
}

?>