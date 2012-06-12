/**
 * Access tickets by workflow item identifier
 *
 * @author tay
 * @version $Id:$
 */
function(doc)
{
    if (doc.type && 'WorkflowTicket' === doc.type)
    {
        emit(doc.item, doc);
    }
}
