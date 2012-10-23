function(doc)
{
    if (doc.type && 'ShofiWorkflowItem' === doc.type && doc.masterRecord.categorySource)
    {
        emit(doc.masterRecord.categorySource, doc);
    }
}
