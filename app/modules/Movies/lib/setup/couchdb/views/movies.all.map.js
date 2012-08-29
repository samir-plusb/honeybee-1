/**
 * Access verticals by key.
 */
function(doc)
{
    if (doc.type && 'MoviesWorkflowItem' === doc.type)
    {
        emit(doc._id, doc);
    }
}
