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
     * Holds the name of our id property.
     */
    const PROP_ASSET_ID = 'id';

    /**
     * Holds the name of our origin property.
     */
    const XPROP_ORIGIN = 'origin';

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
        return (int)$this->assetId;
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
    }

    // ---------------------------------- </IAssetInfo IMPL> -------------------------------------


    // ---------------------------------- <HYDRATE SETTERS> --------------------------------------

    /**
     * Set our origin.
     *
     * @param       string $origin
     */
    protected function setOrigin($origin)
    {
        $this->origin = $origin;
    }

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
     * Sets our fullname.
     *
     * @param       string $name
     */
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

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>