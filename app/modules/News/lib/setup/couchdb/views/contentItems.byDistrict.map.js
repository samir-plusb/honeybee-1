/**
 * Access published content-items by district.
 *
 * @author tay
 * @version $Id:$
 */
function(doc)
{
    if (doc.type && 'NewsWorkflowItem' === doc.type && !doc.attributes.marked_deleted)
    {
        var item;
        for (var i = 0; i < doc.contentItems.length; i++)
        {
            item = doc.contentItems[i];
            if (item.location && item.location.district && item.publishDate)
            {
                emit(item.location.district, item);
            }
        }
    }
}
