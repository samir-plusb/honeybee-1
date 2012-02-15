/**
 * Access published content-items by district.
 *
 * @author tay
 * @version $Id:$
 */
function(key, values, rereduce)
{
    if (!rereduce)
    {
        return values.length;
    }
    return sum(values);
}
