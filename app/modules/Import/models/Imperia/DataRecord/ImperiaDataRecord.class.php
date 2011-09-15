<?php

/**
 * The ImperiaDataRecord class is a concrete implementation of the XmlBasedDataRecord base class.
 * It serves as the base class to all imperia related IDataRecord implementations.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Imperia
 */
abstract class ImperiaDataRecord extends XmlBasedDataRecord
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds the name of our subtitle property.
     */
    const PROP_SUBTITLE = 'subtitle';

    /**
     * Holds the name of our kicker property.
     */
    const PROP_KICKER = 'kicker';
    
    /**
     * Holds the name of our link.
     */
    const PROP_LINK = 'link';

    /**
     * Holds the name of imerpia's directory node.
     */
    const PROP_DIRECTORY = 'directory';
    
    /**
     * Holds the name of imerpia's filename node.
     */
    const PROP_FILENAME = 'filename';
    
    /**
     * Holds the base url to use when generating absolute links.
     * 
     * @todo Move to config cause of evn awareness?
     */
    const LINK_BASE_URL = 'http://www.berlin.de';
    
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    protected $subtitle;
    
    protected $kicker;
    
    protected $directory;
    
    protected $filename;
    
    protected $link;
    
    /**
     * Holds an array with known keys and xpath expressions as values.
     * This $expressionMap is used to evaluate and collect data from a given DOMDocument,
     * that has been initialized with imperia propetary xml.
     * 
     * @var     array 
     */
    protected static $expressionMap = array(
        self::PROP_TITLE     => '/imperia/body/article/title',
        self::PROP_SUBTITLE  => '/imperia/body/article/subtitle',
        self::PROP_KICKER    => '/imperia/body/article/kicker',
        self::PROP_CONTENT   => '/imperia/body/article//paragraph/text',
        self::PROP_CATEGORY  => '/imperia/head/categories/category',
        self::PROP_DIRECTORY => '/imperia/head/directory',
        self::PROP_FILENAME  => '/imperia/head/filename',
        self::PROP_MEDIA     => '/imperia/body/article//image'
    );
    
    protected static $expressionProcessors = array(
            self::PROP_TITLE     => 'extractFirst',
            self::PROP_CONTENT   => 'extractCollection',
            self::PROP_CATEGORY  => 'extractCategory',
            self::PROP_SUBTITLE  => 'extractFirst',
            self::PROP_KICKER    => 'extractFirst',
            self::PROP_DIRECTORY => 'extractFirst',
            self::PROP_FILENAME  => 'extractFirst',
            self::PROP_MEDIA     => 'extractMedia'
        );
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    abstract protected function getSourceName();
    
    // ---------------------------------- <IDataRecord IMPL> -------------------------------------
    
    /**
     * Returns a hopefully unique identifier.
     * 
     * @return      string
     * 
     * @see         IDataRecord::getIdentifier()
     */
    public function getIdentifier()
    {
        return $this->getTitle() . '/' . $this->getSource();
    }
    
    // ---------------------------------- </IDataRecord IMPL> ------------------------------------
    
    
    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------
    
    public function getSubtitle()
    {
        return $this->subtitle;
    }
    
    public function getKicker()
    {
        return $this->kicker;
    }
    
    public function getLink()
    {
        return $this->link;
    }
        
    // ---------------------------------- </PUBLIC METHODS> --------------------------------------
    
    
    // ---------------------------------- <XmlBasedDataRecord IMPL> ------------------------------
    
    /**
     * Return an array holding fieldnames and corresponding xpath queries
     * that will be evaluated and mapped to the correlating field.
     * 
     * @return      array
     * 
     * @see         XmlBasedDataRecord::getFieldMap()
     */
    protected function getFieldMap()
    {
        return self::$expressionMap;
    }
    
    /**
     * Normalize the given xpath results.
     * 
     * @param       array $data Contains result from processing our field map.
     * 
     * @return      array
     * 
     * @see         XmlBasedDataRecord::normalizeData()
     */
    protected function normalizeData(array $xPathResults)
    {
        $data = array();
        
        foreach (self::$expressionProcessors as $propName => $processor)
        {
            if (is_callable(array($this, $processor)))
            {
                $data[$propName] = $this->$processor($xPathResults, $propName);
            }
        }
        
        $data[self::PROP_SOURCE] = $this->getSourceName();
        $data[self::PROP_GEO] = array();
        
        return $data;
    }
    
    protected function getExposedProperties()
    {
        return array_merge(
            parent::getExposedProperties(),
            array(
                self::PROP_SUBTITLE,
                self::PROP_KICKER,
                self::PROP_LINK
            )
        );
            
    }
    
    // ---------------------------------- </XmlBasedDataRecord IMPL> -----------------------------
    
    
    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    protected function extractFirst(array $xPathResults, $key)
    {
        if (!isset($xPathResults[$key]) || 0 === $xPathResults[$key]->length)
        {
            return NULL;
        }
        
        return trim($xPathResults[$key]->item(0)->nodeValue);
    }
    
    protected function extractCollection(array $xPathResults, $key)
    {
        $items = array();
        
        if (!isset($xPathResults[$key]))
        {
            return $items;
        }
        
        foreach ($xPathResults[$key] as $node)
        {
            if (($value = trim($node->nodeValue)))
            {
                $items[] = $value;
            }
        }

        return $items;
    }
    
    protected function extractCategory(array $xPathResults, $key)
    {
        $categoryCrumbs = $this->extractCollection($xPathResults, $key);
        
        return sprintf('// %s', join(' // ', $categoryCrumbs));
    }
    
    protected function extractMedia(array $xPathResults, $key)
    {
        $assets = array();
        
        if (!isset($xPathResults[$key]) || !$xPathResults[$key])
        {
            return $assets;
        }
        
        foreach ($xPathResults[$key] as $imageNode)
        {
            $assets[] = $this->createAsset($imageNode);
        }
        
        return $assets;
    }
    
    protected function createAsset(DOMNode $imageNode)
    {
        $metaDataNodes = array('caption');
        $metaData = array();
        $src = NULL;
        
        foreach ($imageNode->childNodes as $childNode)
        {
            if ('src' === $childNode->nodeName)
            {
                $src = self::LINK_BASE_URL . trim($childNode->nodeValue);
            }
            if (in_array($childNode->nodeName, $metaDataNodes))
            {
                $metaData[$childNode->nodeName] = trim($childNode->nodeValue);
            }
        }
        
        if ($src)
        {
            $assetInfo = ProjectAssetService::getInstance()->put($src, $metaData);
            
            return $assetInfo->getId();
        }
        
        return NULL;
    }
    
    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    
    // ---------------------------------- <HYDRATE SETTERS> --------------------------------------
    
    protected function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }
    
    protected function setKicker($kicker)
    {
        $this->kicker = $kicker;
    }
    
    protected function setDirectory($directory)
    {
        $this->directory = $directory;
        $this->applyLink();
    }
    
    protected function setFilename($filename)
    {
        $this->filename = $filename;
        $this->applyLink();
    }
    
    protected function applyLink()
    {
        if ($this->filename && $this->directory)
        {
            $this->link = sprintf(
                "%s%s/%s",
                self::LINK_BASE_URL,
                $this->directory,
                $this->filename
            );
        }
    }
    
    // ---------------------------------- </HYDRATE SETTERS> -------------------------------------
}

?>