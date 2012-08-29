function(doc)
{
    if (doc.type && 'EventsWorkflowItem' === doc.type && 0 < doc.masterRecord.articles.length)
    {
        for(var i = 0; i < doc.masterRecord.articles.length; i++)
        {
            var article = doc.masterRecord.articles[i];
            if (article.category)
            {
                emit(article.category, article);
            }
        }
    }
}
