<?php

/**
 * The NewsMasterRecord is a data object implementation of the INewsEntity interface.
 * It holds the originally imported unmodified data as provided by the news import
 * and is used as the primary datasource for content-item refinement.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package News
 * @subpackage Workflow/Item
 */
class NewsMasterRecord extends MasterRecord implements INewsEntity
{
    /**
     * Holds the NewsMasterRecord's title.
     *
     * @var string
     */
    protected $title;

    /**
     * Holds the NewsMasterRecord's textual content.
     *
     * @var string
     */
    protected $content;

    /**
     * Holds the NewsMasterRecord's category.
     *
     * @var string
     */
    protected $category;

    /**
     * Holds the NewsMasterRecord's media.
     *
     * @var array
     */
    protected $media;

    /**
     * Holds the NewsMasterRecord's geo data.
     *
     * @var array
     */
    protected $geoData;

    /**
     * Create a fresh NewsWorkflowItem instance from the given the data and return it.
     *
     * Example value structure for the $data argument,
     * which is the same structure as the toArray method's return.
     *
     * <pre>
     * Example value structure:
     * array(
     *  // Meta Data
     *  'parentIdentifier'    => 'foobar',
     *  'created'             => array(
     *      'date' => '05-23-1985T15:23:78.123+01:00',
     *      'user' => 'shrink0r'
     *   ),
     *   'lastModified'       => array(
     *      'date' => '06-25-1985T15:23:78.123+01:00',
     *      'user' => 'shrink0r'
     *    ),
     *    // Content Data
     *    'source'    => 'rss'
     *    'origin'    => 'http://spiegel.de/latest/',
     *    'timestamp' => '2009-28-12T13:25:12.000+1:00'
     *    'title'     => 'This is import item title',
     *    'content'   => 'I am the imported content.',
     *    'category'  => '/some/category/path',
     *    'media'     => array(23, 5, 17, 13),
     *    'geoData'   => array(
     *         'long' => 12.345,
     *         'lat'  => 23.456
     *     )
     * </pre>
     *
     * @param array $data
     *
     * @return IWorkflowItem
     */
    public static function fromArray(array $data = array())
    {
        return new NewsMasterRecord($data);
    }

    /**
     * Returns the NewsMasterRecord's content title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the master record's title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        $this->onPropertyChanged("title");
    }

    /**
     * Returns the NewsMasterRecord's main content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the master record's content.
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
        $this->onPropertyChanged("content");
    }

    /**
     * Returns the NewsMasterRecord's category.
     * The structure of the data carried inside the string may vary depending on our source.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set the master record's category.
     *
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
        $this->onPropertyChanged("category");
    }

    /**
     * Returns a list of id's that can be used together with ProjectAssetService
     * to resolve assets.
     *
     * @return array
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Set the master record's media.
     *
     * @param array $media An array with asset ids.
     */
    public function setMedia(array $media)
    {
        $this->media = $media;
        $this->onPropertyChanged("media");
    }

    /**
     * Returns array holding the geo data associated with the NewsMasterRecord.
     *
     * @return array
     */
    public function getGeoData()
    {
        return $this->geoData;
    }

    /**
     * Set the master record's geoData.
     *
     * @param array $geoData
     */
    public function setGeoData(array $geoData)
    {
        $this->geoData = $geoData;
        $this->onPropertyChanged("geoData");
    }
}

?>
