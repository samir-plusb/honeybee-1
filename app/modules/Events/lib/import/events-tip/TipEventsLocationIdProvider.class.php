<?php

/**
 * The TipEventsLocationIdProvider class maps tip sublocations to their correspondig master locations.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Events
 * @subpackage      Import/EventsTip
 */
class TipEventsLocationIdProvider
{
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
     * Holds a assoc array that maps sublocations to their parents.
     *
     * @var array $locationMap
     */
    protected $locationMap;

    /**
     * Create a new TipEventsLocationIdProvider instance,
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

        $this->locationMap = array();
        foreach ($xpath->query('//tbo:veranstaltungsort') as $locationNode)
        {
            $adressId = $locationNode->getAttribute('key');
            $masterAddress = $xpath->query('.//tbo:masteradresse', $locationNode)->item(0);
            $masterAddress = $masterAddress ? $masterAddress->nodeValue : $adressId;
            $this->locationMap[$adressId] = $masterAddress;
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
        return isset($this->locationMap[$uid]);
    }

    /**
     * Returns a locations parent uid.
     * 
     * @param string $uid
     *
     * @return string Returns parent location's uid or null if we don't know the uid.
     */
    public function getParentUid($uid)
    {
        return $this->hasUid($uid) ? $this->locationMap[$uid] : NULL;
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
            throw new XmlParserException("Validation for document failed.");
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
