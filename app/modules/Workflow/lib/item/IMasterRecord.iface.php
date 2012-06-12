<?php

/**
 * The IMasterRecord interface extends the IDocument interface to add in members,
 * that hold arbitary data and information on where that (imported)data came from.
 * It is assumed that every workflow process is based on some kind of initial data.
 * The master record's job is to provide this (meta-)data to it's parent workflow-item
 * and maintain it in an unmodified state.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Workflow
 * @subpackage Item
 */
interface IMasterRecord extends IDocument
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
     * Returns the MasterRecord's source,
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
     * that is associated with the MasterRecord's content.
     *
     * @return string
     */
    public function getTimestamp();
}

?>
