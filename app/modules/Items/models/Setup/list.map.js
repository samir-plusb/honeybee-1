/**
 * List import items by time
 *
 * @author schrink0r
 * @version $Id:$
 */
function(doc)
{
    var key = null;

    if (doc.timestamp)
    {
        key = doc.timestamp;
    }

    emit(key, doc);
}