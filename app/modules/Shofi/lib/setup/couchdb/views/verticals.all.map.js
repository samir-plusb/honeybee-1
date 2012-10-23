/**
 * Access verticals by key.
 */
function(doc)
{
    if (doc.type && 'ShofiVerticalsWorkflowItem' === doc.type)
    {
        emit(doc._id, doc);
    }
}
