/**
 * Access tickets with workflow items included sorted by timestamp.
 *
 * @author Thorsten Schmitt-Rink
 * @version $Id:$
 */
function(doc)
{
    if (doc.type && 'WorkflowTicket' === doc.type)
    {
        var list_item = { '_id': doc.item };
        for (var attr in doc)
        {
            if (0 !== attr.indexOf('_') && 'item' !== attr) // ignore internal fields & include item
            {
                list_item[attr] = doc[attr];
            }
        }
        emit(doc.ts, list_item);
    }
}
