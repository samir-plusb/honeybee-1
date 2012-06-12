<?php

/**
 * The NitfNewswireXmlParser class is a concrete implementation of the BaseXmlParser base class.
 * It provides parsing of the neswire-nitf xml format.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Import/Newswire
 */
class NitfNewswireXmlParser extends BaseXmlParser
{
    // ---------------------------------- <BaseXmlParser IMPL> -----------------------------------

    protected function process(DOMXpath $xpath)
    {
        $xpathExpressions = array(
            'doc-id' => '//doc-id/@id-string',
            'title' => '//head/title',
            'headline' => '//hedline/hl1',
            'abstract' => '//abstract',
            'content' => '//body.content/p',
            'fixture-id' => '//fixture/@fix-id',
            'copyright' => '//doc.copyright/@holder',
            'keywords' => '//keyword/@key'
        );

        return array_merge(
            $this->evaluateXpaths($xpath, $xpathExpressions),
            array(
                'media' => $this->importMedia($xpath),
                'table' => $this->importTable($xpath),
                'date-issue' => $this->fetchNodeValueAsDate($xpath, '//date.issue/@norm'),
                'date-release' => $this->fetchNodeValueAsDate($xpath, '//date.release/@norm'),
                'date-expire' => $this->fetchNodeValueAsDate($xpath, '//date.expire/@norm'),
            )
        );
    }

    // ---------------------------------- </BaseXmlParser IMPL> ----------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * import nitf tables
     *
     * @param       DOMDocument $domDoc
     *
     * @return      array of xml tagged strings
     */
    protected function importTable(DOMXPath $xpath)
    {
        $data = array();
        foreach ($xpath->query('//table') as $table)
        {
            $data[] = self::nodeToString($table);
        }
        return $data;
    }

    /**
     * import image media objects
     *
     * @param       DOMNode $item current nitf document
     * @param       array $feed_values feed entry values
     *
     * @return      array with reference information
     */
    protected function importMedia(DOMXPath $xpath)
    {
        $media = array();
        foreach ($xpath->query("//media[@media-type='image']") as $mediaNode)
        {
            $pixels = -1;
            $image = array();
            foreach ($xpath->query('.//media-reference', $mediaNode) as $mediaReference)
            {
                $attribute = $mediaReference->attributes;
                $width = intval($attribute->getNamedItem("width")->nodeValue);
                $height = intval($attribute->getNamedItem("height")->nodeValue);
                if ($pixels < ($width * $height))
                {
                    $image['source'] = htmlspecialchars($attribute->getNamedItem("source")->nodeValue);
                    $image['name'] = htmlspecialchars($attribute->getNamedItem("name")->nodeValue);
                    $image['alternate'] = htmlspecialchars($attribute->getNamedItem("alternate-text")->nodeValue);
                    $pixels = $width * $height;
                }
            }
            if (! empty ($image))
            {
                $captionNodeList = $xpath->query('media-caption', $mediaNode);
                $image['caption'] = self::joinNodeList($captionNodeList, "\n");
                $media[] = $image;
            }
        }
        return $media;
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>
