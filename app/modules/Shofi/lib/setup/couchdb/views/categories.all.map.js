/**
 * Access published content-items by district.
 *
 * @author tay
 * @version $Id:$
 */
function(doc)
{
    if (doc.type && 'ShofiCategoriesWorkflowItem' === doc.type)
    {
        emit(doc._id, doc);
    }
}
