function(doc)
{
    if (doc.type && 'EventsWorkflowItem' === doc.type && doc.masterRecord.category)
    {
        emit(doc.masterRecord.category, doc);
    }
}
