<?php

/**
 * The DpaNitfNewswireDataRecord class is a concrete implementation of the NitfNewswireDataRecord base class.
 * It provides processing data in the nitf format for data coming from the dpa.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <tom.anheyer@berlinonline.de>
 * @package         News
 * @subpackage      Import/Newswire
 */
class DpaNitfNewswireDataRecord extends NitfNewswireDataRecord
{
    /**
     * Holds the name of links property
     */
    const PROP_LINKS = 'links';

    /**
     * Holds our link array
     *
     * @var         string
     */
    protected $links;

    /**
     * get links data
     *
     *  @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

    // ---------------------------------- <XmlDataRecord IMPL> ------------------------------

    /**
     * Filter the collected data.
     *
     * Maps existing xml node lists to strings or array of strings.
     *
     * @see         XmlDataRecord::normalizeData()
     *
     * @return      array
     */
    protected function parseData($data)
    {
        $parentData = parent::parseData($data);

        $parser = new DpaNitfNewswireXmlParser();
        $parsedData = $parser->parseXml($data);

        $data = $parentData;
        list($data[self::PROP_IDENT]) = explode(':', $parsedData['doc-id']);
        $data[self::PROP_LINKS] = $parsedData['links'];
        $data[self::PROP_COPYRIGHT] = $parsedData['copyright'];
        if (isset($parentData[self::PROP_KEYWORDS]) && $parentData[self::PROP_KEYWORDS])
        {
            $list = array();
            foreach ($parentData[self::PROP_KEYWORDS] as $keyword)
            {
                $list = array_merge($list, explode('/', $keyword));
            }
            $data[self::PROP_KEYWORDS] = array_filter($list);
        }
        return $data;
    }

    // ---------------------------------- </XmlDataRecord IMPL> -----------------------------


    // ---------------------------------- <NewsDataRecord OVERRIDES> -----------------------

    /**
     * Return an array holding property names of properties,
     * which we want to expose through our IDataRecord::toArray() method.
     *
     * @return      array
     */
    protected function getExposedProperties()
    {
        return array_merge(
            parent::getExposedProperties(),
            array(
                self::PROP_LINKS,
            )
        );
    }

    // ---------------------------------- </NewsDataRecord OVERRIDES> ----------------------
}

?>