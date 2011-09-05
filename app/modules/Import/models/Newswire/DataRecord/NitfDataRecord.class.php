<?php
/**
 * NITF processing
 *
 * @package Import
 * @subpackage Newswire
 * @version $ID:$
 * @author Tom Anheyer
 *
 */
class NitfNewswireDataRecord extends NewswireDataRecord
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
        return array(
            'id' => '//doc-id/@id-string',
            'title' => '//head/title',
            'subtitle' => '//hedline/hl2',
            'abstract' => '//abstract',
            'copyright' => '//doc.copyright/@holder',
            'source' => '//meta[@name="origin"]/@content',
            'date.issue' => '//date.issue/@norm',
            'date.release' => '//date.release/@norm',
            'date.expire' => '//date.expire/@norm',
            'catchline' => '//fixture/@fix-id',
            'keywords' => '//keyword/@key',
            'body' => '//body.content/p',
        );
    }



    /**
     * acts like join() for the nodeValues of the node-list
     *
     * @param DOMNodeList $nl
     * @param string $separator
     * @return string
     */
    protected function joinNodeList(DOMNodeList $nl, $separator)
    {
        $content = $this->nodeListToArray($nl);
        return join($separator, $content);
    }


    /**
     * translate a node list to a simple string array
     *
     * @param DOMNodeList $nl
     */
    protected function nodeListToArray(DOMNodeList $nl)
    {
        $content = array();
        for ($i = 0; $i < $nl->length; $i++) {
            $content[] = self::nodeToString($nl->item($i));
        }
        return $content;
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
        if ($data['body'] instanceof DOMNodeList)
        {
            $data['body'] = $this->joinNodeList($data['body'], "\n\n");
        }

        $data['keywords'] = ($data['keywords'] instanceof DOMNodeList)
            ? $this->nodeListToArray($data['keywords'])
            : array($data['keywords']);

        foreach ($data as $key => $value)
        {
            if ($value instanceof DOMNodeList)
            {
                if (0 == $value->length)
                {
                    $data['key'] = '';
                }
                else
                {
                    $map = $this->getFieldMap();
                    throw new DataImportException('Value for xpath "'.$map[$key].'" results to a list. Expected is a scalar');
                }
            }
        }
        $data = array_filter($data);
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
        $data['media'] = $this->importMedia($domDoc);
        return $data;
    }

    /**
     * import image media objects
     *
     * @param DOMNode $item current nitf document
     * @param array $feed_values feed entry values
     * @return array with reference information
     */
    protected function importMedia(DOMDocument $domDoc)
    {
        $media = array();
        $xpath = new DOMXPath($domDoc);
        foreach ($xpath->query("//media[@media-type='image']") as $mNode)
        {
            $pixels = -1;
            $img = array();
            foreach ($xpath->query('//media-reference', $mNode) as $mr) {
                $attr = $mr->attributes;
                $width = intval($attr->getNamedItem("width")->nodeValue);
                $height = intval($attr->getNamedItem("height")->nodeValue);
                if ($pixels < ($width * $height)) {
                    $img['source'] = htmlspecialchars($attr->getNamedItem("source")->nodeValue);
                    $img['name'] = htmlspecialchars($attr->getNamedItem("name")->nodeValue);
                    $img['alternate'] = htmlspecialchars($attr->getNamedItem("alternate-text")->nodeValue);
                    $pixels = $width * $height;
                }
            }
            if (! empty ($img))
            {
                $cnl = $xpath->query('/media-caption', $mNode);
                $img['caption'] = $this->joinNodeList($cnl, "\n");
                $media[] = $img;
            }
        }
        return $ref_values;
    }


}