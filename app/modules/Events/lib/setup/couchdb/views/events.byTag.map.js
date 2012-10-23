function(doc)
{
    if (doc.type && 'EventsWorkflowItem' === doc.type && 0 < doc.masterRecord.tags.length)
    {
        for (var i = 0; i < doc.masterRecord.tags.length; i++)
        {
            emit(doc.masterRecord.tags[i], doc);
        }
    }
}
