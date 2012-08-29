function(doc)
{
    if (doc.type && 'EventsWorkflowItem' === doc.type && doc.masterRecord.categorySrc)
    {
        emit(doc.masterRecord.categorySrc, doc);
    }
}
