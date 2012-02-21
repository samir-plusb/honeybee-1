<?php

/**
 * The DpaNitfNewswireDataRecord class is a concrete implementation of the NitfNewswireDataRecord base class.
 * It provides processing data in the nitf format for data coming from the dpa.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <tom.anheyer@berlinonline.de>
 * @package         Import
 * @subpackage      Newswire
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

    /**
     * set links member from hydrate
     *
     * @param array $links
     */
    protected function setLinks($links)
    {
        $this->links = $links;
    }

    // ---------------------------------- <XmlBasedDataRecord IMPL> ------------------------------

    /**
     * Return a list of field keys to corresponding xpath expressions.
     *
     * @see         collectData()
     * @see         XmlBasedDataRecord::getFieldMap()
     *
     * @return      array
     */
    protected function getFieldMap()
    {
        return array_merge(
            parent::getFieldMap(),
            array(
                self::PROP_SUBTITLE  => '//byline',
                self::PROP_COPYRIGHT => '//meta[@name="copyright"]/@content',
                self::PROP_SOURCE    => '//meta[@name="origin"]/@content'
            )
        );
    }

    /**
     * Filter the collected data.
     *
     * Maps existing xml node lists to strings or array of strings.
     *
     * @see         XmlBasedDataRecord::normalizeData()
     *
     * @return      array
     */
    protected function normalizeData(array $data)
    {
        $normalized = parent::normalizeData($data);
        list($normalized[self::PROP_IDENT]) = explode(':', $data[self::PROP_IDENT]->item(0)->nodeValue);
        $normalized[self::PROP_LINKS] = $this->importLinks();

        if (isset($data[self::PROP_KEYWORDS]) && $data[self::PROP_KEYWORDS])
        {
            $list = array();

            foreach ($data[self::PROP_KEYWORDS] as $keyword)
            {
                $list = array_merge($list, explode('/', $keyword->nodeValue));
            }

            $normalized[self::PROP_KEYWORDS] = array_filter($list);
        }

        return $normalized;
    }

    // ---------------------------------- </XmlBasedDataRecord IMPL> -----------------------------


    // ---------------------------------- <ImportBaseDataRecord OVERRIDES> -----------------------

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

    // ---------------------------------- </ImportBaseDataRecord OVERRIDES> ----------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Import nitf tables.
     *
     * @param       DOMDocument $domDoc
     *
     * @return      array of xml tagged strings
     */
    protected function importLinks()
    {
        $data = array();
        $domDoc = $this->getDocument();
        $xpath = new DOMXPath($domDoc);

        foreach ($xpath->query('//body.content/block[@style="EXTERNAL-LINKS"]/p/a') as $node)
        {
            $data[] = $this->nodeToString($node);
        }

        return $data;
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>