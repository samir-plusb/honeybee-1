function(doc)
{
    if (doc.origin)
    {
        emit(doc.origin, doc);
    }
}
