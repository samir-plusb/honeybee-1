function(doc)
{
    if (doc.type && 'EventsWorkflowItem' === doc.type && null !== doc.masterRecord.archive)
    {
        emit(doc.masterRecord.archive.identifier, doc.masterRecord.archive);
    }
}
