<?php

/**
 * Hold the import item data
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 * @since 24.10.2011
 *
 */
class WorkflowItem implements IDataRecord
{
    /**
     * holds our data
     *
     * @var array
     */
    protected $data;

    /**
     * initialize item from assoziative array
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * implement universal member variable access
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return array_key_exists($name, $this->data) ? $this->data[$name] : NULL;
    }


    /**
     * Return an unique string that identifies this record.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->__get(self::PROP_IDENT);
    }

    /**
     * Return this IDataRecord's source.
     * Will usually be a name or term related to the datasource
     * that created this record instance.
     *
     * @return      string
     */
    public function getSource()
    {
        return $this->__get(self::PROP_SOURCE);
    }

    /**
     * Return this IDataRecord's timestamp.
     *
     * can be record last change time, message issue date, mail date, ...
     *
     * @return      string
     */
    public function getTimestamp()
    {
        return $this->__get(self::PROP_TIMESTAMP);
    }

    /**
     * Return this IDataRecord's data origin (url, filepath eg).
     *
     * @return      string
     */
    public function getOrigin()
    {
        return $this->__get(self::PROP_ORIGIN);
    }

    /**
     * Returns our title.
     *
     * @return      string
     */
    public function getTitle()
    {
        return $this->__get(self::PROP_TITLE);
    }

    /**
     * Returns our content.
     *
     * @return      string
     */
    public function getContent()
    {
        return $this->__get(self::PROP_CONTENT);
    }

    /**
     * Returns our category.
     *
     * @return      string
     */
    public function getCategory()
    {
        return $this->__get(self::PROP_CATEGORY);
    }

    /**
     * Returns our media (image, video and file assets for example).
     * The returned value is an array holding id's that can be used together with our ProjectAssetService
     * implementations.
     * Example return value structure:
     * -> return array(23, 24, 512, 13);
     *
     * @return      array
     */
    public function getMedia()
    {
        return $this->__get(self::PROP_MEDIA);
    }

    /**
     * Returns our geo data in the following structure:
     * -> return array(
     *        'long' => $longValue,
     *        'lat'  => $latValue
     *    );
     *
     * @return      array
     */
    public function getGeoData()
    {
        return $this->__get(self::PROP_GEO);
    }

    /**
     * (non-PHPdoc)
     * @see IDataRecord::toArray()
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * (non-PHPdoc)
     * @see IDataRecord::validate()
     */
    public function validate()
    {
        return array('ok' => TRUE);
    }
}