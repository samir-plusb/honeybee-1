<?php

/**
 * The TipEventsPersonProvider class provides access to person data contained by the person-export.xml file,
 * which is currently delivered together with the other eventx data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Events
 * @subpackage      Import/Xml
 */
class TipEventsPersonProvider
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
     * Holds a assoc array that maps person uids/keys to person data rows.
     *
     * @var array $uidPersonMap
     */
    protected $uidPersonMap;

    /**
     * Create a new TipEventsPersonProvider instance,
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

        $this->uidPersonMap = array();
        foreach ($xpath->query('//tbo:person') as $personNode)
        {
            $key = $personNode->getAttribute('key');
            $personData = array('key' => $key);
            if (($node = $xpath->query('.//tip:text', $personNode)->item(0)))
            {
                $personData['text'] = $node->nodeValue;
            }
            if (($node = $xpath->query('.//tip:name', $personNode)->item(0)))
            {
                $personData['name'] = $node->nodeValue;
            }
            $this->uidPersonMap[$key] = $personData;
        }
    }

    /**
     * Tells whether we have a person for the given uid/key.
     *
     * @param string $uid
     *
     * @return bool
     */
    public function hasUid($uid)
    {
        return isset($this->uidPersonMap[$uid]);
    }

     /**
     * Returns an person data row for the given person uid/key.
     * 
     * @param string $uid
     *
     * @return array Returns null if there is no person for the given uid.
     */
    public function getPersonByUid($uid)
    {
        return $this->hasUid($uid) ? $this->uidPersonMap[$uid] : NULL;
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
