<?php

/**
 * The MasterRecord is a base implementation of the IMasterRecord interface.
 * It implements the basic interface and serves as base class to all concrete IMasterRecord implementations.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Workflow
 * @subpackage Item
 */
abstract class MasterRecord extends BaseDocument implements IMasterRecord
{
    /**
     * Holds the MasterRecord's parentIdentifier.
     *
     * @var string
     */
    protected $parentIdentifier;

    /**
     * Holds the MasterRecord's source.
     *
     * @var string
     */
    protected $source;

    /**
     * Holds the MasterRecord's origin.
     *
     * @var string
     */
    protected $origin;

    /**
     * Holds the MasterRecord's content timestamp.
     * Should be a ISO8601 UTC date string.
     *
     * @var string
     */
    protected $timestamp;

    /**
     * Returns the unique identifier of our aggregate root (IWorkflowItem).
     *
     * @return string
     *
     * @see IWorkflowItem::getIdentifier()
     */
    public function getParentIdentifier()
    {
        return $this->parentIdentifier;
    }

    /**
     * Returns the MasterRecord's source,
     * hence a string representing the content provider that delivered the data.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Returns an uri pointing to the resource that we originate from.
     * Always is a uri, but may hold a custom scheme.
     *
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Returns an ISO8601 UTC date string that holds a timestamp,
     * that is associated with the MasterRecord's content.
     *
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set the master record's timestamp.
     *
     * @param type $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        $this->onPropertyChanged("timestamp");
    }
}

?>
