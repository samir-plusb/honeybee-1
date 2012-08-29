/**
 * Access verticals by key.
 */
function(doc)
{
    if (doc.type && 'EventsWorkflowItem' === doc.type)
    {
        emit(doc._id, doc);
    }
}
