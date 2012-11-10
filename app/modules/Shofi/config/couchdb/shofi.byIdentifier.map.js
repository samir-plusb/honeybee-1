/**
 * Access shofi all places by id.
 */
function(doc)
{
    if (doc.type && 'ShofiWorkflowItem' === doc.type)
    {
        emit(doc._id, doc);
    }
}

