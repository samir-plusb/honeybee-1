/**
 * Access all UserWorkflowItem tickets by identifier.
 */
function(doc)
{
    if (doc.type && 'UserWorkflowItem' === doc.type)
    {
        emit(doc._id, doc.ticket);
    }
}
