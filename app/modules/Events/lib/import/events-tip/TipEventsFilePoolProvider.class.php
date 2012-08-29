<?php

/**
 * The TipEventsFilePoolProvider class provides access to article data contained by the *-export-FP.xml files,
 * which are currently delivered together with the *-export.xml files from our TIP-Import source location.
 *
 * @version         $Id: TipEventsFilePoolProvider.class.php 1299 2012-06-12 16:09:14Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Events
 * @subpackage      Import/Xml
 */
class TipEventsFilePoolProvider
{
    /**
     * Holds a assoc array that maps asset uids/keys to their corresponding file data.
     *
     * @var array $uidFileMap
     */
    protected $uidFileMap;

    /**
     * Holds a assoc array that maps event uids/keys to their corresponding file data.
     *
     * @var array $eventIdFileMap
     */
    protected $eventIdFileMap;

    /**
     * Parse and load the given xml file into our uid map.
     *
     * @param string $xmlFilepath
     */
    public function load($xmlFilepath)
    {
        if (! is_readable($xmlFilepath))
        {
            throw new XmlParserException("Document at uri: '$xmlFilepath' is not readable.");
        }

        $document = new DOMDocument('1.0', 'utf-8');
        $document->load($xmlFilepath);
        $xpath = $this->createXpath($document, array($document->namespaceURI));

        $this->uidFileMap = array();
        $this->eventIdFileMap = array();
        foreach ($xpath->query('//x:Veranstaltung') as $eventFilesNode)
        {
            $eventId = $eventFilesNode->getAttribute('VId');
            $assetData = $this->parseEventFilesNode($xpath, $eventFilesNode);
            foreach ($assetData as $assetEntry)
            {
                $uid = $assetEntry['uuid'];
                $assetEntry['eventId'] = $eventId;
                if (! isset($this->uidFileMap[$uid]))
                {
                    $this->uidFileMap[$uid] = array();
                }
                $this->uidFileMap[$uid][] = $assetEntry;
            }
            $this->eventIdFileMap[$eventId] = $assetData;
        }
    }

    /**
     * Tells whether we have assets for the given filepool uid/key.
     *
     * @param string $uid
     *
     * @return bool
     */
    public function hasUid($uid)
    {
        return isset($this->uidFileMap[$uid]);
    }

    /**
     * Returns the asset data for a given filepool uid/key.
     * 
     * @param string $uid
     *
     * @return array Returns null if there is no filepool entry for the given uid.
     */
    public function getFileByUid($uid)
    {
        return $this->hasUid($uid) ? $this->uidFileMap[$uid] : NULL;
    }

    /**
     * Parse the given list of event-files nodes into an unified array represention and return it.
     *
     * @param DOMXPath $xpath
     * @param DOMElement $eventFilesNode
     *
     * @return array
     */
    protected function parseEventFilesNode(DOMXPath $xpath, DOMElement $eventFilesNode)
    {
        static $intVals = array('width', 'height', 'filesize');
        static $nodes = array(
            'x:mime', 'x:width', 'x:height', 'x:filesize', 'x:uuid', 
            'x:slot', 'x:location', 'x:copyright', 'x:caption'
        );

        $data = array();
        foreach ($xpath->query('.//x:asset', $eventFilesNode) as $assetNode)
        {
            $assetData = array();
            foreach ($nodes as $nodeName)
            {
                $plainName = str_replace('x:', '', $nodeName);
                if (($node = $xpath->query(sprintf('.//%s', $nodeName), $assetNode)->item(0)))
                {
                    if (in_array($plainName, $intVals))
                    {
                        $assetData[$plainName] = (int)$node->nodeValue;
                    }
                    else
                    {
                        $assetData[$plainName] = trim($node->nodeValue);
                    }
                }
            }
            $data[] = $assetData;
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
        $xpath = new DOMXPath($document);
        foreach ($namespaces as $namespace)
        {
            $xpath->registerNamespace(
                NULL === $namespace ? 'x' : $namespace,
                $document->lookupNamespaceUri($namespace)
            );
        }

        return $xpath;
    }
}
