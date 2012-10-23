<?php

/**
 * The TipEventsArticleProvider class provides access to article data contained by the artikel-export.xml file,
 * which is currently delivered together with the other eventx data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Events
 * @subpackage      Import/Xml
 */
class TipEventsArticleProvider
{
    /**
     * Holds the name of the directory that we'll look for when searching article assets.
     */
    const ASSET_DIRECTORY = 'bilder';

    /**
     * Holds the scheme to use along with asset uris that we will build,
     * while parsing articles.
     */
    const ASSET_SCHEME = 'file://';

    /**
     * Holds the namespace prefix of the tip (main) namespace.
     */
    const TIP_NAMESPACE_PREFIX = 'tip';

    /**
     * Holds the namespace prefix of the tbo (tip-berlinonline) vendor namespace.
     */
    const TBO_NAMESPACE_PREFIX = 'tbo';

    /**
     * Holds a path to a xsd schema used to validate xml files before pulling data from them.
     *
     * @var string $schema
     */
    protected $schema;

    /**
     * Holds a assoc array that maps article keys to article data rows.
     *
     * @var array $uidArticleMap
     */
    protected $uidArticleMap;

    /**
     * Holds a TipEventsPersonProvider instance used to resolve our person references.
     *
     * @var TipEventsPersonProvider $personProvider
     */
    protected $personProvider; 

    /**
     * Create a new TipEventsArticleProvider instance,
     * thereby providing a xsd schema location to use for data prevalidation.
     *
     * @param string $schema
     */
    public function __construct($schema)
    {
        $this->schema = realpath($schema);

        if (! $this->schema || ! is_readable($this->schema))
        {
            throw new InvalidArgumentException(
                "The given schema '" . $this->schema . "' is not readable!"
            );
        }
    }

    /**
     * Set the person provider to use when parsing event documents.
     *
     * @param TipEventsPersonProvider $personProvider
     */
    public function setPersonProvider(TipEventsPersonProvider $personProvider = NULL)
    {
        $this->personProvider = $personProvider;
    }

    /**
     * Parse and load the given xml file into our uid map.
     *
     * @param string $xmlFilepath
     */
    public function load($xmlFilepath)
    {
        if (! is_readable($xmlFilepath))
        {
            throw new InvalidArgumentException("Document at uri: '$xmlFilepath' is not readable.");
        }

        $document = new DOMDocument('1.0', 'utf-8');
        $document->load($xmlFilepath);
        $xpath = $this->createXpath($document, array(self::TBO_NAMESPACE_PREFIX, self::TIP_NAMESPACE_PREFIX));

        $assetDir = $this->getAssetDirectoryBy($document);

        $this->uidArticleMap = array();
        foreach ($xpath->query('//tbo:artikel') as $articleNode)
        {
            $articleData = $this->parseArticleNode($xpath, $articleNode);
            $key = $articleNode->getAttribute('key');
                        
            $assetUri = FALSE;
            if (! empty($articleData['bildURL']))
            {
                $assetPath = $assetDir . DIRECTORY_SEPARATOR . $articleData['bildURL'];
                if (is_readable($assetPath))
                {
                    $assetUri = self::ASSET_SCHEME . $assetPath;
                }
                else
                {
                    echo "Unable to read asset from location: " . $assetPath . PHP_EOL;
                }
            }
            if ($assetUri)
            {
                $articleData['bildURL'] = $assetUri;
            }
            else
            {
                unset($articleData['bildURL']);
            }
            $this->uidArticleMap[$key] = $articleData;
        }
    }

    /**
     * Tells whether we have an article for the given uid/key.
     *
     * @param string $uid
     *
     * @return bool
     */
    public function hasUid($uid)
    {
        return isset($this->uidArticleMap[$uid]);
    }

    /**
     * Returns an article data row for the given article uid/key.
     * 
     * @param string $uid
     *
     * @return array Returns null if there is no article for the given uid.
     */
    public function getArticleByUid($uid)
    {
        return $this->hasUid($uid) ? $this->uidArticleMap[$uid] : NULL;
    }

    /**
     * Determine the article asset directory for the given document.
     *
     * @var DOMDocument $document
     *
     * @return string
     */
    protected function getAssetDirectoryBy(DOMDocument $document)
    {
        $documentDir = dirname($document->documentURI);
        $assetDir = realpath($documentDir . DIRECTORY_SEPARATOR . self::ASSET_DIRECTORY);

        if (! is_readable($assetDir))
        {
            throw new InvalidArgumentException(
                "No asset directory was found for the given article file: '$assetDir' is not readable."
            );
        }

        return $assetDir;
    }

    /**
     * Parses data coming from a 'tbo:artikel' node
     * and returns a unified array reflecting the contained data.
     *
     * Schema definition for 'tbo:artikel' nodes:
     *  <complexType name="artikel">
     *      <sequence>
     *          <element name="aktDat" type="tip:DatumZeit"/>
     *          <element ref="tip:name"/>
     *          <element ref="tip:text"/>
     *          <element name="mediaFile" type="tip:Id" minOccurs="0"/>
     *          <element name="gesperrt" type="tip:True" minOccurs="0"/>
     *          <element name="typ" type="tip:Textfeld"/>
     *          <element name="autor" type="tip:Textfeld" minOccurs="0"/>
     *          <element name="author" type="tip:Textfeld" minOccurs="0"/>
     *          <element name="heft" type="tip:Textfeld" minOccurs="0"/>
     *          <element name="subtitel" type="tip:Textfeld" minOccurs="0"/>
     *          <group ref="tbo:bild" minOccurs="0"/>
     *          <element name="bx" type="integer" minOccurs="0"/>
     *          <element name="by" type="integer" minOccurs="0"/>
     *          <element name="bu" type="tip:Textfeld" minOccurs="0"/>
     *          <element name="bf" type="tip:Textfeld" minOccurs="0"/>
     *          <element name="archiveintrag" type="tbo:ArchivReferenz" minOccurs="0" maxOccurs="unbounded"/>
     *          <element name="person" type="tbo:PersonenReferenz" minOccurs="0" maxOccurs="unbounded"/>
     *          <element name="ortstermin" type="tbo:OrtsterminReferenz" minOccurs="0" maxOccurs="unbounded"/>
     *          <element name="veranstaltung" type="tbo:VeranstaltungsReferenz" minOccurs="0" maxOccurs="unbounded"/>
     *          <element name="veranstaltungsort" type="tbo:VeranstaltungsortReferenz" minOccurs="0" maxOccurs="unbounded"/>
     *      </sequence>
     *      <attribute name="key" use="required" type="tbo:ArtikelKey"/>
     *  </complexType>
     *
     * @param DOMXpath $xpath
     * @param DOMElement $articleNode
     *
     * @return array
     */
    protected function parseArticleNode(DOMXPath $xpath, DOMElement $articleNode)
    {
        static $intNodes = array('tbo:mediaFile', 'tip:bx', 'tip:by');
        static $refNodes = array('tbo:archiveintrag', 'tbo:veranstaltung', 'tbo:veranstaltungsort');
        static $textNodes = array(
            'tbo:aktDat', 'tip:name', 'tip:text', 'tbo:typ', 'tbo:autor', 
            'tbo:author', 'tbo:heft', 'tbo:subtitel', 'tbo:bildURL', 'tbo:bildTyp', 'tbo:bu', 'tbo:bf'
        );

        $cleanName = function($name) { return str_replace(array('tbo:', 'tip:'), '', $name); };
        $data = array('key' => $articleNode->getAttribute('key'));
        foreach ($textNodes as $nodeName)
        {
            if (($node = $xpath->query(sprintf('.//%s', $nodeName), $articleNode)->item(0)))
            {
                $data[$cleanName($nodeName)] = trim($node->nodeValue);
            }
        }

        foreach ($intNodes as $nodeName)
        {
            if (($node = $xpath->query(sprintf('.//%s', $nodeName), $articleNode)->item(0)))
            {
                $data[$cleanName($nodeName)] = (int)$node->nodeValue;
            }
        }

        foreach ($refNodes as $nodeName)
        {
            $plainName = $cleanName($nodeName);
            if (! isset($data[$plainName]))
            {
                $data[$plainName] = array();
            }
            if (($node = $xpath->query(sprintf('.//%s', $nodeName), $articleNode)->item(0)))
            {
                $data[$plainName][] = $node->getAttribute('foreignkey');
            }
        }

        $data['person'] = array();
        foreach ($xpath->query(sprintf('.//tbo:person', $nodeName), $articleNode) as $personRefNode)
        {
            $data['person'][] = $this->personProvider->getPersonByUid(
                $personRefNode->getAttribute('foreignkey')
            );
        }

        if (($node = $xpath->query('.//tbo:gesperrt', $articleNode)->item(0)))
        {
            $data['gesperrt'] = (bool)$node->nodeValue;
        }

        return $data;
    }

    /**
     * Convenience method that creates a xpath instance
     * with the given namespaces allready registered.
     * 
     * @param DOMDocument $document
     * @param array $namespaces
     *
     * @return DOMXPath
     */
    protected function createXpath(DOMDocument $document, array $namespaces = array())
    {
        if (! $document->schemaValidate($this->schema))
        {
            throw new XmlParserException(
                "Validation for document " . $document->documentURI . " failed."
            );
        }

        $xpath = new DOMXPath($document);
        foreach ($namespaces as $namespace)
        {
            $xpath->registerNamespace(
                $namespace, 
                $document->lookupNamespaceUri($namespace)
            );
        }
        return $xpath;
    }
}
