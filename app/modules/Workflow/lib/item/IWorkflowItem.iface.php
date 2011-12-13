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
     * Bump the item's revision.
     *
     * @param string $revision
     *
     * @return IWorkflowItem This instance for fluent api support.
     */
    public function bumpRevision($revision);

    /**
     * Return the identifier of our ticket, if we have one.
     *
     * @return string
     */
    public function getTicketId();

    /**
     * Set the item's parent ticket.
     *
     * @param WorkflowTicket $ticket
     *
     * @return IWorkflowItem This instance for fluent api support.
     */
    public function setTicket(WorkflowTicket $ticket);

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
     * Update the item's modified timestamp.
     * If the created timestamp has not yet been set it also assigned.
     *
     * @param AgaviUser $user An optional user to use instead of resolving the current session user.
     *
     * @return IWorkflowItem This instance for fluent api support.
     */
    public function touch(AgaviUser $user = NULL);

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
     * Return the item's current state in the workflow,
     * meaning the workflow step it's in and who owns it at the moment.
     *
     * @return array
     */
    public function getCurrentState();

    /**
     * Updates the item's current workflow state (step and owner).
     *
     * @param string $state
     *
     * @return IWorkflowItem This instance for fluent api support.
     */
    public function updateCurrentState(array $state);

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

    /**
     * Returns an array representation of the IWorkflowItem.
     *
     * <pre>
     * Example value structure:
     * array(
     *     // Meta Data
     *     'identifier'   => 'foobar',
     *     'revision'     => '1-15394a6853828769ee1be885909548b3',
     *     'ticketId'     => '12jk1hjh132jbasdl2',
     *     'created'             => array(
     *         'date' => '05-23-1985T15:23:78.123+01:00',
     *         'user' => 'shrink0r'
     *     ),
     *     'lastModified' => array(
     *         'date' => '06-25-1985T15:23:78.123+01:00',
     *         'user' => 'shrink0r'
     *     ),
     *     'importItem'   => @see IImportItem::toArray(),
     *     'contentItems' => array(
     *         1 => @see IContentItem::toArray(),
     *         2 => ...,
     *         ...
     *     ),
     *     'attributes' => array(
     *         'someKey' => 'over the value',
     *         ...
     *     )
     * )
     * </pre>
     *
     * @return array
     */
    public function toArray();
}

?>
