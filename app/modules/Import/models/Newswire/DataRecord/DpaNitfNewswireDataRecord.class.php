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
        );
        return array_merge(parent::getFieldMap(), $dmap);
    }

    /**
     * collect data from xml document
     *
     * @uses getFieldMap()
     * @uses importMedia()
     * @see XmlBasedDataRecord::collectData()
     */
    protected function collectData(DOMDocument $domDoc)
    {
        $data = parent::collectData($domDoc);
        $data['links'] = $this->importLinks($domDoc);

        return $data;
    }

    /**
     * import nitf tables
     *
     * @param DOMDocument $domDoc
     * @return array of xml tagged strings
     */
    protected function importLinks(DOMDocument $domDoc)
    {
        $data = array();
        $xpath = new DOMXPath($domDoc);
        foreach ($xpath->query('//body.content/block[@style="EXTERNAL-LINKS"]/p/a') as $nd)
        {
            $data[] = $this->nodeToString($nd);
        }
        return $data;
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

        list($data['id'], $data['revision']) = explode(':', $data['id']);

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