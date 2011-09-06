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
            'links' => '//body.content/block[@style="EXTERNAL-LINKS"]/p',
        );
        return array_merge(parent::getFieldMap(), $dmap);
    }

    /**
     * filter the collected data.
     *
     * Maps existing xml node lists to strings or array of strings.
     *
     * @see XmlBasedDataRecord::normalizeData()
     * @return array
     */
    protected function normalizeData(array $data)
    {
        $data = parent::normalizeData($data);
        if (! empty($data['keywords']))
        {
            $list = array();
            foreach ($data['keywords'] as $kw)
            {
                $list = array_merge($list,explode('/', $kw));
            }
            $data['keywords'] = array_filter($list);
        }
        return $data;
    }
}

?>