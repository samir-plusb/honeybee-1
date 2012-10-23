<?php

/**
 * The EventsWorkflowItem extends the WorkflowItem and serves as the aggregate root for all aggregated event data objects.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Events
 * @subpackage Workflow/Item
 */
class EventsWorkflowItem extends WorkflowItem
{
    /**
     * Factory method for creating new EventsWorkflowItem instances.
     *
     * @var array $data
     *
     * @return EventsWorkflowItem
     */
    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    /**
     * Returns the initial workflow to use for EventsWorkflowItem instances.
     *
     * @return string Resolvable name of the workflow to use.
     */
    public function determineWorkflow()
    {
        return 'Events';
    }

    /**
     * Returns the name of the class to use when creating new master record instances.
     *
     * @return string
     */
    protected function getMasterRecordImplementor()
    {
        return "EventsMasterRecord";
    }
}
