function(doc)
{
    if (doc.type && 'EventsWorkflowItem' === doc.type && doc.masterRecord.origin)
    {
        emit(doc.masterRecord.origin, doc);
    }
}
