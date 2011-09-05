<?php
/**
 * DPA specific NITF processing
 *
 * @package Import
 * @subpackage Newswire
 * @version $ID:$
 * @author Tom Anheyer
 *
 */
class DpaNitfNewswireDataRecord extends NitfNewswireDataRecord
{

    /**
     * return a list of field keys to corresponding xpath expressions
     *
     * @see collectData()
     * @see XmlBasedDataRecord::getFieldMap()
     * @return array
     */
    protected function getFieldMap()
    {
        $dmap = array(
            'subtitle' => '//byline',
            'copyright' => '//meta[@name="copyright"]/@content',
            'source' => '//meta[@name="origin"]/@content',
            'tables' => '//body.content/table',
            'links' => '//body.content/block[@style="EXTERNAL-LINKS"]/p',
        );
        return array_merge(parent::getFieldMap(), $dmap);
    }
}