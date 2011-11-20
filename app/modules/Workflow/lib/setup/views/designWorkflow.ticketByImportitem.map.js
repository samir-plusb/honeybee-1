/**
 * Access tickets by import item identifier
 *
 * @author tay
 * @version $Id:$
 */
function(doc)
{
    if (doc.item)
    {
        emit(doc.item, null);
    }
}
