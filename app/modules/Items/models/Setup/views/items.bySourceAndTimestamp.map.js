/**
 * List import items by source and time
 *
 * @author tay
 * @version $Id:$
 */
function(doc) {
  emit([doc.source,doc.timestamp], doc.timestamp);
}