<?php

/**
 * The IWorkflowItem interface defines the data structure that is pushed through the workflow
 * in order to refine and distribute our companies (imported) content.
 * The latter data seperates into four main sections that are aggregated by this interface,
 * hence it serves as the aggregate root for the underlying domain data structure.
 * The following entities are nested into a IWorkflowItem:
 *
 * - MasterRecord: Holds the raw imported data together with some meta infos about the data's lifecycle and origin.
 *                 An MasterRecord is never modified from outside the import and after it is initially created,
 *                 it may be updated in cases where the delivering content provider supports/sends updates.
 *                 Updates are reflected by a difference between the values for the created and the modified fields.
 *
 * - Attributes  : A genric collection of arbitary key=>value pairs,
 *                 that can be used to augment/expand the defined set of domain data without violating the interface.
 *
 * -     -*-     : In most concrete implementation scenarios, besides containing a MasterRecord and Attributes,
 *                 a IWorkflowItem implementation will mostly also want to capture user data etc.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Workflow
 * @subpackage Item
 */
interface IWorkflowItem extends IDocument
{
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
    public function setRevision($revision);

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
    public function setCurrentState(array $state);

    /**
     * Return our related import item.
     *
     * @return IMasterRecord
     */
    public function getMasterRecord();

    /**
     * Set an import-item for this workflow-item instance.
     *
     * @param mixed $masterRecord
     */
    public function setMasterRecord($record);

    /**
     * Create a fresh IMasterRecord instance from the given data
     * and set ourself as the master-reocrd's parent.
     *
     * @param array $data
     *
     * @return IMasterRecord
     */
    public function createMasterRecord(array $data);

    /**
     * Update the workflow-item's import item with the given values.
     *
     * @param array $masterData
     */
    public function updateMasterRecord(array $data);

    /**
     * Return a generic assoc array of attributes.
     *
     * @return array A plain key=>value collection.
     */
    public function getAttributes();

    /**
     * Set the value for a given attribute name.
     *
     * @param string $name The name of the attribute to set.
     * @param mixed $value The attribute value to set.
     */
    public function setAttribute($name, $value);

    /**
     * Return the value for a given attribute name.
     *
     * @param string $name The name of the attribute to get the value for.
     * @param mixed $default The value to return if the attribute is not set.
     *
     * @return mixed The attribute value or $default if the attribute is not set.
     */
    public function getAttribute($name, $default = NULL);
}

?>
