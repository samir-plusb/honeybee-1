<?php

/**
 * The IWorkflowItem interface defines the data structure that is pushed through the workflow
 * in order to refine and distribute our companies (imported) content.
 * The latter data seperates into four main sections that are aggregated by this interface,
 * hence it serves as the aggregate root for the underlying domain data structure.
 * The following entities are nested into a IWorkflowItem:
 *
 * - ImportItem: Holds the raw imported data together with some meta infos about the data's lifecycle and origin.
 *               An ImportItem is never modified from outside the import and after it is initially created,
 *               it may be updated in cases where the delivering content provider supports/sends updates.
 *               Updates are reflected by a difference between the values for the created and the modified fields.
 *
 * - ContentItems: A collection of different data units that have been gained from processing an ImportItem.
 *                 These reflect the data that actually produces our companies ROI and are mainly created/refined by
 *                 real life editors that work themselves through the workflow system until the purified content items
 *                 are distributed to the targeted consumers.
 *
 * - Attributes: A genric collection of arbitary key=>value pairs,
 *               that can be used to augment/expand the defined set of domain data without violating the interface.
 *
 * @version $Id:$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Workflow
 * @subpackage Item
 */
interface IWorkflowItem
{
    /**
     * Returns the system wide unique identifier of the IWorkflowItem.
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Returns the IWorkflowItem's current revision.
     *
     * @return string
     */
    public function getRevision();

    /**
     * Returns the IContentItem's created date as an array,
     * containing data about by whom and when the item was created.
     * The provided date data is a ISO8601 UTC formatted string.
     * The provided user information is a string holding the username.
     *
     * <pre>
     * Value structure example:
     * array(
     *     'date' => '05-23-1985T15:23:78.123+01:00',
     *     'user' => 'shrink0r'
     * )
     * </pre>
     *
     * @return array
     */
    public function getCreated();

    /**
     * Returns the IContentItem's created date as an array,
     * containing data about by whom and when the item modified the last time.
     * The provided date data is a ISO8601 UTC formatted string.
     * The provided user information is a string holding the username.
     *
     * <pre>
     * Value structure example:
     * array(
     *     'date' => '05-25-1985T15:23:78.123+01:00',
     *     'user' => 'shrink0r'
     * )
     * </pre>
     *
     * @return array
     */
    public function getLastModified();

    /**
     * Return our related import item.
     *
     * @return IImportItem
     */
    public function getImportItem();

    /**
     * Return a list of content items that belong to this workflow item.
     *
     * @return array An list of of IContentItems
     */
    public function getContentItems();

    /**
     * Return a generic assoc array of attributes.
     * @todo Implement an AttributeHolder for this?
     *
     * @return array A plain key=>value collection.
     */
    public function getAttributes();
}

?>
