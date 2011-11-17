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
     * Returns the ImportItem's unique identifier.
     *
     * @return string
     */
    public function getIdentifier();

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
     * @return array Example: array(23, 5, 42);
     */
    public function getMedia();

    /**
     * Returns array holding the geo data associated with the ImportItem.
     *
     * @return array Example: array('long' => 12.345, 'lat' => 67.890)
     */
    public function getGeoData();
}

?>
