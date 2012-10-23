/**
 * Access tickets with workflow items included sorted by timestamp.
 *
 * @author Thorsten Schmitt-Rink
 * @version $Id$
 */
function(doc)
{
    if (doc.type && 'WorkflowTicket' === doc.type)
    {
        emit(doc._id, doc);
    }
}
