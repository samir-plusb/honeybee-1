<?php

/**
 * The IImportItem interface defines the data structure that is used to hold raw imported data and pass it around.
 * IImportItems are aggregated by an IWorkflowItem (aggregate root) and may not exist on their own.
 * In most cases IImportItems will be created together with IWorkflowItems within a WorkflowItemDataImport execution.
 * After creation IImportItems serve as the primary data source to the IContentItems,
 * that are then created within our aggregate root during the following content refinement process.
 *
 * @version $Id:$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Workflow
 * @subpackage Item
 */
interface IImportItem
{
    /**
     * Returns the unique identifier of our aggregate root (IWorkflowItem).
     *
     * @return string
     *
     * @see IWorkflowItem::getIdentifier()
     */
    public function getParentIdentifier();

    /**
     * Returns a ISO8601 UTC date string that holds the creation date.
     *
     * @return string
     */
    public function getCreated();

    /**
     * Returns a ISO8601 UTC date string that holds the last modification date.
     *
     * @return string
     */
    public function getLastModified();

     /**
     * Returns the ImportItem's source,
     * hence a string representing the content provider that delivered the data.
     *
     * @return string
     */
    public function getSource();

    /**
     * Returns an uri pointing to the resource that we originate from.
     * Always is a uri, but may hold a custom scheme.
     *
     * @return string
     */
    public function getOrigin();

    /**
     * Returns an ISO8601 UTC date string that holds a timestamp,
     * that is associated with the ImportItem's content.
     *
     * @return string
     */
    public function getTimestamp();

    /**
     * Returns the ImportItem's content title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Returns the ImportItem's main content.
     *
     * @return string
     */
    public function getContent();

    /**
     * Returns the ImportItem's category.
     * The structure of the data carried inside the string may vary depending on our source.
     *
     * @return string
     */
    public function getCategory();

    /**
     * Returns a list of id's that can be used together with ProjectAssetService
     * to resolve assets.
     *
     * <pre>
     * Example value structure:
     * array(23, 5, 42)
     * </pre>
     * @return array
     */
    public function getMedia();

    /**
     * Returns array holding the geo data associated with the ImportItem.
     *
     * <pre>
     * Example value structure:
     * array(
     *     'long' => 12.345,
     *     'lat' => 67.890
     * )
     * </pre>
     *
     * @return array
     */
    public function getGeoData();

    /**
     * Returns an array representation of the import item.
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
     * @return string
     */
    public function toArray();
}

?>
