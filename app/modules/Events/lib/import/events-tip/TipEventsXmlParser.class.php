<?php

/**
 * The TipEventsXmlParser class is a concrete implementation of the IXmlParser interface
 * and parses eventx xml files to an unified array structure.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Events
 * @subpackage      Import/Xml
 */
class TipEventsXmlParser implements IXmlParser
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
     * Holds the null value representation for *provider properties.
     */
    const PROVIDERL_NONE = NULL;

    /**
     * Holds a path pointing to an xsd schema file,
     * that used for validating documents before they are processed.
     *
     * @var string $schema
     */
    protected $schema; 

    /**
     * Holds a TipEventsFilePoolProvider instance used to resolve our filepool references,
     *
     * @var TipEventsFilePoolProvider $filePoolProvider
     */
    protected $filePoolProvider;

    /**
     * Holds a TipEventsPersonProvider instance used to resolve our person references,
     *
     * @var TipEventsPersonProvider $personProvider
     */
    protected $personProvider; 

    /**
     * Holds a TipEventsArticleProvider instance used to resolve our article references,
     *
     * @var TipEventsArticleProvider $articleProvider
     */
    protected $articleProvider; 

    /**
     * Holds a TipEventsLocationIdProvider that resolves sublocation ids to master ids. 
     *
     * @var TipEventsLocationIdProvider
     */
    protected $locationIdProvider;

    /**
     * Create a new TipEventsXmlParser instance.
     *
     * @param string $schema
     */
    public function __construct($schema)
    {
        $this->schema = realpath($schema);

        if (! $this->schema || ! is_readable($this->schema))
        {
            throw new XmlParserException(
                "The given schema '" . $this->schema . "' is not readable!"
            );
        }
    }

    /**
     * Set the filepool to use when parsing event documents.
     *
     * @param TipEventsFilePoolProvider $filePoolProvider
     */
    public function setFilePoolProvider(TipEventsFilePoolProvider $filePoolProvider = NULL)
    {
        $this->filePoolProvider = $filePoolProvider;
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
     * Set the article provider to use when parsing event documents.
     *
     * @param TipEventsArticleProvider $articleProvider
     */
    public function setArticleProvider(TipEventsArticleProvider $articleProvider = NULL)
    {
        $this->articleProvider = $articleProvider;
    }

    /**
     * Set the location-id provider to use when parsing event documents.
     *
     * @param TipEventsLocationIdProvider $locationIdProvider
     */
    public function setLocationIdProvider(TipEventsLocationIdProvider $locationIdProvider = NULL)
    {
        $this->locationIdProvider = $locationIdProvider;
    }

    // ---------------------------------- <IXmlParser IMPL> --------------------------------------

    /**
     * Parse the given xml file and return an unified array structure.
     *
     * @param string $xmlFilepath
     *
     * @return array
     */
    public function parseXml($xmlFilepath)
    {
        if (! is_readable($xmlFilepath))
        {
            throw new XmlParserException("Document at uri: '$xmlFilepath' is not readable.");
        }

        $document = new DOMDocument('1.0', 'utf-8');
        $document->load($xmlFilepath);

        return $this->processDocument($document);
    }

    // ---------------------------------- </IXmlParser IMPL> -------------------------------------


    // ---------------------------------- <PARSING METHODS> --------------------------------------

    /**
     * Returns an array representation of all 'tbo:veranstaltung' nodes found
     * in the given document. {@see self::CONTAINER_NODE}
     *
     * Schema definition for 'tbo:veranstaltung' nodes:
     *  <complexType name="veranstaltung">
     *      <annotation>
     *          <documentation>
     *              <![CDATA[
     *                  Veranstaltungen. Alle abgeleiteten Typen entsprechen diesem und
     *                  existieren nur aus historischen Gründen.
     *              ]]>
     *          </documentation>
     *      </annotation>
     *      <sequence>
     *          <element ref="tip:name"/>
     *          <element ref="tip:text" minOccurs="0"/>
     *          <element name="werke" type="tip:Textfeld" minOccurs="0"/>
     *          <element name="aktDat" type="tip:DatumZeit"/>
     *          <sequence minOccurs="0">
     *              <annotation>
     *                  <documentation>
     *                      <![CDATA[
     *                          sequence wird nicht genommen fuer geloeschte Veranstaltungen, aber fuer
     *                          alle anderen! Laesst sich leider nicht sauber ausdruecken, da es dem aktuellen
     *                          export entsprechen muss.
     *                      ]]>
     *                  </documentation>
     *              </annotation>
     *              <element name="erfDat" type="tip:DatumZeit"/>
     *              <choice>
     *                  <group ref="tbo:FilmVeranstaltung"/>
     *                  <group ref="tbo:Veranstaltung"/>
     *              </choice>
     *              <element name="filepool" type="tip:Textfeld" minOccurs="0" maxOccurs="unbounded"/>
     *          </sequence>
     *      </sequence>
     *      <attribute name="key" use="required" type="tbo:VeranstaltungsKey"/>
     *  </complexType>
     *
     * @param DOMDocument $document
     * 
     * @return array
     */
    protected function processDocument(DOMDocument $document)
    {
        static $textNodes = array(
            'tip:name',  'tip:text', 'tbo:aktDat', 'tbo:erfDat', 'tbo:archiv', 
            'tbo:originaltitel', 'tbo:sortiertitel', 'tbo:publikum', 'tbo:tickets', 'tbo:buch', 
            'tbo:dauer', 'tbo:treffpunkt', 'tbo:highlight', 'tbo:kinder', 'tbo:werke'
        );
        static $complexNodes = array(
            'tbo:artikel',  'tbo:eintrittspreis', 'tbo:archiv', 'tbo:stile', 'tbo:altersangabe', 
            'tbo:mitwirkende', 'tbo:ortstermine'
        );
        static $boolNodes = array('tbo:tip-punkt', 'tbo:gesperrt');

        $xpath = $this->createXpath($document, array(self::TBO_NAMESPACE_PREFIX, self::TIP_NAMESPACE_PREFIX));

        $data = array();
        foreach ($xpath->query('//tbo:veranstaltung') as $eventNode)
        {
            $eventData = array_merge(
                $this->fetchTextNodeValues($xpath, $eventNode, $textNodes),
                $this->fetchBoolNodeValues($xpath, $eventNode, $boolNodes),
                $this->parseComplexData($xpath, $eventNode, $complexNodes),
                array(
                    'identifier' => $eventNode->getAttribute('key'),
                    'type' => $this->fetchPlainNodeName($eventNode->getAttribute('xsi:type'))
                )
            );

            $eventData['filepool'] = $this->fetchFilePoolData(
                $xpath->query('.//tbo:filepool', $eventNode)
            );
            $data[] = $eventData;
        }

        return $data;
    }

    /**
     * 
     *
     * @param DOMNodeList $filePoolNodes
     * 
     * @return array
     */
    protected function fetchFilePoolData(DOMNodeList $filePoolNodes)
    {
        $data = array();

        if (0 < $filePoolNodes->length && ! $this->filePoolProvider)
        {
            echo "Unable to resolve filepool references without a valid FilepoolProvider given." . PHP_EOL;
        }
        else
        {
            foreach ($filePoolNodes as $filePoolNode)
            {
                $fileUid = trim($filePoolNode->nodeValue);
                if ($this->filePoolProvider->hasUid($fileUid))
                {
                    $data[] = $this->filePoolProvider->getFileByUid($fileUid);
                }
                else
                {
                    echo "Can not resolve filepool reference for uid: " . $fileUid . PHP_EOL;
                }
            }
        }

        return $data;
    }

    /**
     * Takes care of fetching the values from all elements,
     * that are considered as 'complex'.
     * @see self::$complexValueNodes
     *
     * @param DOMXPath $xpath
     * @param DOMElement $element
     *
     * @return array
     */
    protected function parseComplexData(DOMXPath $xpath, DOMElement $relativeTo, array $nodeNames)
    {
        $data = array();
        foreach ($nodeNames as $nodeName)
        {
            $nodeList = $xpath->query(sprintf('.//%s', $nodeName), $relativeTo);
            if (0 >= $nodeList->length)
            {
                continue;
            }

            $plainName = $this->fetchPlainNodeName($nodeName);
            $parseMethod = sprintf('parse%s', ucfirst($plainName));
            if (is_callable(array($this, $parseMethod)))
            {
                $data[$plainName] = $this->$parseMethod($xpath, $nodeList);
            }
        }

        return $data;
    }

    /**
     * Parses data coming from the 'tbo:eintrittspreis' nodelist
     * and returns a unified array reflecting the node's contained data.
     *
     * Schmema defintion:
     *  <complexType name="VEintrittspreis">
     *      <simpleContent>
     *          <extension base="tip:Textfeld">
     *              <attribute name="art">
     *                  <simpleType>
     *                      <restriction base="string">
     *                          <enumeration value="Vorverkauf"/>
     *                          <enumeration value="Abendkasse"/>
     *                          <enumeration value="kostenlos"/>
     *                      </restriction>
     *                  </simpleType>
     *              </attribute>
     *          </extension>
     *      </simpleContent>
     *  </complexType>
     *
     * @param DOMXpath $xpath
     * @param DOMNodeList $nodes
     *
     * @return array
     */
    protected function parseEintrittspreis(DOMXPath $xpath, DOMNodeList $nodes)
    {
        $containerNode = $nodes->item(0);
        return array(
            'type' => $containerNode->getAttribute('art'),
            'text' => trim($containerNode->nodeValue)
        );
    }

    /**
     * Parses data coming from a 'tbo:person' nodelist (person references)
     * and returns a unified array reflecting the contained data 
     * of the resolved person references.
     *
     * Schema definition:
     *  <complexType name="Mitwirkende">
     *      <sequence>
     *          <element name="person" maxOccurs="unbounded">
     *              <complexType>
     *                  <simpleContent>
     *                      <extension base="tip:Textfeld">
     *                          <attribute name="foreignkey" type="tbo:PersonenKey" use="required"/>
     *                      </extension>
     *                  </simpleContent>
     *              </complexType>
     *          </element>
     *      </sequence>
     *  </complexType>
     *
     * @param DOMXpath $xpath
     * @param DOMNodeList $nodes
     *
     * @return array
     */
    protected function parseMitwirkende(DOMXPath $xpath, DOMNodeList $nodes)
    {
        $people = array();

        if (0 < $nodes->length && ! $this->personProvider)
        {
            echo "Unable to resolve people references without a valid PersonProvider given." . PHP_EOL;
        }
        else
        {
            foreach ($xpath->query('.//tbo:person', $nodes->item(0)) as $personNode)
            {
                $uid = $personNode->getAttribute('foreignkey');

                if ($this->personProvider->hasUid($uid))
                {
                    $personData = $this->personProvider->getPersonByUid($uid);
                    $people[] = array(
                        'person' => $personData,
                        'text' => trim($personNode->nodeValue)
                    );
                }
                else
                {
                    echo "Can not resolve person reference for uid: " . $uid . PHP_EOL;
                }
            }
        }

        return $people;
    }

    /**
     * Parses data coming from a 'tbo:stile' nodelist 
     * and returns a unified array reflecting the contained data.
     *
     * Schema definition:
     *  <complexType name="stile">
     *      <sequence>
     *          <element name="stil" type="tip:Textfeld" maxOccurs="unbounded"/>
     *      </sequence>
     *  </complexType>
     *
     * @param DOMXpath $xpath
     * @param DOMNodeList $nodes
     *
     * @return array
     */
    protected function parseStile(DOMXPath $xpath, DOMNodeList $nodes)
    {
        $styles = array();
        if (0 === $nodes->length)
        {
            return $styles;
        }

        foreach ($xpath->query('.//tbo:stil', $nodes->item(0)) as $styleNode)
        {
            $styles[] = trim($styleNode->nodeValue);
        }

        return $styles;
    }

    /**
     * Parses data coming from a 'tbo:altersangabe' nodelist
     * and returns a unified array reflecting the contained data.
     *
     * Schema definition:
     *  <complexType name="Altersangabe">
     *      <sequence>
     *          <element name="alter_von" type="tip:Textfeld" minOccurs="0" form="unqualified"/>
     *          <element name="alter_bis" type="tip:Textfeld" minOccurs="0" form="unqualified"/>
     *      </sequence>
     *  </complexType>
     *
     * @param DOMXpath $xpath
     * @param DOMNodeList $nodes
     *
     * @return array
     */
    protected function parseAltersangabe(DOMXPath $xpath, DOMNodeList $nodes)
    {
        if (0 === $nodes->length)
        {
            return NULL;
        }

        $containerNode = $nodes->item(0);
        $fromNode = $xpath->query('.//tbo:alter_von',$containerNode)->item(0);
        $toNode = $xpath->query('.//tbo:alter_bis', $containerNode)->item(0);

        return array(
            'from' => $fromNode ? trim($fromNode->nodeValue) : NULL,
            'to' => $toNode ? trim($toNode->nodeValue) : NULL
        );
    }

    /**
     * Parses data coming from a 'tbo:archiv' nodelist
     * and returns a unified array reflecting the contained data.
     *
     * Schema definition:
     *  <complexType name="Archiv">
     *      <sequence>
     *          <element ref="tip:text" minOccurs="0"/>
     *          <element name="aktDat" type="tip:DatumZeit"/>
     *          <element name="erfDat" type="tip:DatumZeit"/>
     *          <element name="originaltitel" type="tip:Textfeld" minOccurs="0"/>
     *          <element name="sortiertitel" type="tip:Textfeld" minOccurs="0"/>
     *          <element name="bewertung" type="tip:gt0Integer" minOccurs="0"/>
     *          <element name="tip-punkt" type="tip:True" minOccurs="0"/>
     *          <element name="mitwirkende" type="tbo:Mitwirkende" minOccurs="0"/>
     *          <element name="filmNr" type="integer"/>
     *          <element name="filepool" type="tip:Textfeld" minOccurs="0" maxOccurs="unbounded"/>
     *          <element name="fsk" type="tip:Textfeld" minOccurs="0"/>
     *          <element name="kinderfilm" type="tip:True" minOccurs="0"/>
     *          <element name="vorlage" type="tip:Textfeld" minOccurs="0"/>
     *          <element name="filmreihe" type="tip:Textfeld" minOccurs="0"/>
     *          <element name="dauer" type="tip:Textfeld" minOccurs="0"/>
     *          <element name="jahr" type="tip:Textfeld" minOccurs="0"/>
     *          <element name="land" type="tip:Textfeld" minOccurs="0"/>
     *          <element name="filmstart" type="tip:Textfeld" minOccurs="0"/>
     *          <element name="stile" type="tbo:stile" minOccurs="0"/>
     *      </sequence>
     *      <attribute name="foreignkey" type="tbo:ArchivKey" use="required"/>
     *  </complexType>
     *
     * @param DOMXpath $xpath
     * @param DOMNodeList $nodes
     *
     * @return array
     */
    protected function parseArchiv(DOMXPath $xpath, DOMNodeList $nodes)
    {
        if (0 === $nodes->length)
        {
            return NULL;
        }

        static $textNodes = array(
            'tbo:aktDat', 'tbo:erfDat', 'tbo:originaltitel', 'tbo:sortiertitel', 'tbo:fsk', 'tbo:vorlage',
            'tbo:filmreihe', 'tbo:dauer', 'tbo:jahr', 'tbo:land', 'tbo:filmstart', 'tip:text'
        );
        static $boolNodes = array('tbo:kinderfilm', 'tbo:tip-punkt');
        static $intNodes = array('tbo:filmNr', 'tbo:bewertung');

        $archiveNode = $nodes->item(0);
        $data = array_merge(
            array('foreignkey' => $archiveNode->getAttribute('foreignkey')),
            $this->fetchIntNodeValues($xpath, $archiveNode, $intNodes),
            $this->fetchBoolNodeValues($xpath, $archiveNode, $boolNodes),
            $this->fetchTextNodeValues($xpath, $archiveNode, $textNodes)
        );
        $data['filepool'] = $this->fetchFilePoolData($xpath->query('.//tbo:filepool', $archiveNode));
        $data['stile'] = $this->parseStile($xpath, $xpath->query('.//tbo:stile', $archiveNode));
        $data['mitwirkende'] = $this->parseMitwirkende($xpath, $xpath->query('.//tbo:mitwirkende', $archiveNode));

        return $data;
    }

    /**
     * Parses data coming from a 'tbo:artikel' nodelist (article references)
     * and returns a unified array reflecting the contained data 
     * of the resolved article references.
     *
     * @param DOMXpath $xpath
     * @param DOMNodeList $nodes
     *
     * @return array
     */
    protected function parseArtikel(DOMXPath $xpath, DOMNodeList $nodes)
    {
        $articles = array();

        if (0 === $nodes->length || ! $this->articleProvider)
        {
            echo "Unable to resolve article references without a valid ArticleProvider given." . PHP_EOL;
        }
        else
        {
            foreach ($nodes as $articleNode)
            {
                $uid = $articleNode->getAttribute('key');
                $priority = $articleNode->getAttribute('prioritaet');
                if ($this->articleProvider->hasUid($uid))
                {
                    $articleData = $this->articleProvider->getArticleByUid($uid);
                    $articles[] = array(
                        'article' => $articleData,
                        'priority' => $priority,
                        'text' => trim($articleNode->nodeValue)
                    );
                }
                else
                {
                    echo "Can not resolve article reference for uid: " . $uid . PHP_EOL;
                }
            }
        }

        return $articles;
    }

    /**
     * Parses data coming from a 'tbo:ortstermine' nodelist
     * and returns a unified array reflecting the contained data.
     *
     * Schema definition for 'tbo:ortstermin' nodes:
     *  <complexType name="Ortstermin">
     *      <sequence>
     *          <element name="veranstaltungsort" type="tbo:VeranstaltungsortReferenz"/>
     *          <element name="mitwirkende" type="tbo:Mitwirkende" minOccurs="0"/>
     *          <element name="termin" type="tbo:Termin" minOccurs="0" maxOccurs="unbounded"/>
     *      </sequence>
     *  </complexType>
     *
     * @param DOMXpath $xpath
     * @param DOMNodeList $nodes
     *
     * @return array
     */
    protected function parseOrtstermine(DOMXPath $xpath, DOMNodeList $nodes)
    {
        $data = array();
        
        if (0 === $nodes->length)
        {
            return $data;
        }

        foreach ($xpath->query('.//tbo:ortstermin', $nodes->item(0)) as $locationNode)
        {
            $locationRefNode = $xpath->query('.//tbo:veranstaltungsort', $locationNode)->item(0);
            $location = array(
                'veranstaltungsort' =>  $this->locationIdProvider->getParentUid(
                    $locationRefNode->getAttribute('foreignkey')
                )
            );

            $appointments = array();
            foreach ($xpath->query('.//tbo:termin', $locationNode) as $appointmentNode)
            {
                $appointments[] = $this->fetchEventAppointmentData($xpath, $appointmentNode);
            }
            $location['termine'] = $appointments;

            $involvedPeople = $xpath->query('.//tbo:mitwirkende', $locationNode);
            if (0 < $involvedPeople->length)
            {
                $location['mitwirkende'] = $this->parseMitwirkende($xpath, $involvedPeople);
            }

            $data[] = $location;
        }

        return $data;
    }

    /**
     * Parses data coming from a 'tbo:termin' node
     * and returns a unified array reflecting the contained data.
     *
     * Schema definition for 'tbo:termin' nodes:
     *  <complexType name="Termin">
     *      <sequence>
     *          <element name="aktDat" type="tip:DatumZeit"/>
     *          <element name="tagestipp" type="tip:Textfeld" minOccurs="0" />
     *          <element name="starttermin" type="tbo:Starttermin"/>
     *          <element name="endtermin" type="tbo:Endtermin" minOccurs="0"/>
     *          <element name="mitwirkende" type="tbo:Mitwirkende" minOccurs="0"/>
     *          <element name="vortext" type="tip:Textfeld" minOccurs="0"/>
     *          <element name="nachtext" type="tip:Textfeld" minOccurs="0"/>
     *          <group ref="tbo:TerminText" minOccurs="0"/>
     *          <element name="einzelheit" type="tip:Textfeld" minOccurs="0"/>
     *      </sequence>
     *      <attribute name="key" type="tbo:OrtsterminKey" use="required"/>
     *  </complexType>
     *
     * @param DOMXpath $xpath
     * @param DOMElement $eventDateNode
     *
     * @return array
     */
    protected function fetchEventAppointmentData(DOMXPath $xpath, DOMElement $appointmentNode)
    {
        static $textNodes = array(
            'tbo:aktDat', 'tbo:tagestipp', 'tbo:vortext', 'tbo:nachtext', 'tbo:einzelheit', 
            'tip:filmreihe', 'tip:gruppentext'
        );
        static $dateNodes = array('tbo:starttermin', 'tbo:endtermin');

        $data = $this->fetchTextNodeValues($xpath, $appointmentNode, $textNodes);
        $data['key'] = $appointmentNode->getAttribute('key');

        foreach ($dateNodes as $nodeName)
        {
            if (($node = $xpath->query(sprintf('.//%s', $nodeName), $appointmentNode)->item(0)))
            {
                $data[$this->fetchPlainNodeName($nodeName)] = $this->fetchDateData($xpath, $node);
            }
        }

        $involvedPeople = $xpath->query('.//tbo:mitwirkende', $appointmentNode);
        if (0 < $involvedPeople->length)
        {
            $data['mitwirkende'] = $this->parseMitwirkende($xpath, $involvedPeople);
        }

        return $data;
    }

    /**
     * Parses data coming from a 'tbo:starttermin' or 'tbo:endtermin' node
     * and returns a unified array reflecting the contained data.
     *
     * Schema definition for 'tbo:starttermin' and 'tbo:endtermin' nodes:
     *  <complexType name="Starttermin">
     *      <sequence>
     *          <element name="bittmann_termin_id" type="tip:Textfeld" minOccurs="0"/>
     *          <element ref="tip:datum"/>
     *          <element ref="tip:zeit" minOccurs="0"/>
     *      </sequence>
     *  </complexType>
     *  <complexType name="Endtermin">
     *      <sequence>
     *          <element ref="tip:datum"/>
     *          <element ref="tip:zeit" minOccurs="0"/>
     *      </sequence>
     *  </complexType>
     *
     * @param DOMXpath $xpath
     * @param DOMElement $dateNode
     *
     * @return array
     */
    protected function fetchDateData(DOMXPath $xpath, DOMElement $dateNode)
    {
        static $textNodes = array('tip:bittmann_termin_id', 'tip:datum', 'tip:zeit');
        return $this->fetchTextNodeValues($xpath, $dateNode, $textNodes);
    }

    // ---------------------------------- <PARSING METHODS> --------------------------------------


    // ---------------------------------- <HELPER METHODS> ---------------------------------------

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
                "Validation for document: " . $document->documentURI . " failed."
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

    /**
     * Takes a node name and removes the tbo and tip namespace prefixes from it.
     * :info: You might want to make this method less specific for later resuse.
     *
     * @param string $nodeName
     *
     * @return string
     */
    protected function fetchPlainNodeName($nodeName)
    {
        $pattern = sprintf('/(%s|%s):/', self::TBO_NAMESPACE_PREFIX, self::TIP_NAMESPACE_PREFIX);
        return preg_replace($pattern, '', $nodeName);
    }

    /**
     * Fetch the values for a list of nodenames relative to a given element.
     * The values are returned as an array of corresponding text values indexed by nodename.
     *
     * @param DOMXPath $xpath
     * @param DOMElement $relativeTo
     * @param array $nodeNames
     *
     * @return array
     */
    protected function fetchTextNodeValues(DOMXPath $xpath, DOMElement $relativeTo, array $nodeNames)
    {
        return $this->fetchNodeValues($xpath, $relativeTo, $nodeNames, function($value)
        {
            return trim($value);
        });
    }

    /**
     * Fetch the values for a list of nodenames relative to a given element.
     * The values are returned as an array of corresponding integer values indexed by nodename.
     *
     * @param DOMXPath $xpath
     * @param DOMElement $relativeTo
     * @param array $nodeNames
     *
     * @return array
     */
    protected function fetchIntNodeValues(DOMXPath $xpath, DOMElement $relativeTo, array $nodeNames)
    {
        return $this->fetchNodeValues($xpath, $relativeTo, $nodeNames, function($value)
        {
            return (int)$value;
        });
    }

    /**
     * Fetch the values for a list of nodenames relative to a given element.
     * The values are returned as an array of corresponding boolean values indexed by nodename.
     *
     * @param DOMXPath $xpath
     * @param DOMElement $relativeTo
     * @param array $nodeNames
     *
     * @return array
     */
    protected function fetchBoolNodeValues(DOMXPath $xpath, DOMElement $relativeTo, array $nodeNames)
    {
        return $this->fetchNodeValues($xpath, $relativeTo, $nodeNames, function($value)
        {
            return (bool)$value;
        });
    }

    /**
     * Fetch the values for a list of nodenames relative to a given element.
     * Each value is passed to the given cast function 
     * and the result then indexed to it's correspondig nodename.
     *
     * @param DOMXPath $xpath
     * @param DOMElement $relativeTo
     * @param array $nodeNames
     *
     * @return array
     */
    protected function fetchNodeValues(DOMXPath $xpath, DOMElement $relativeTo, array $nodeNames, $castFunction)
    {
        $values = array();
        foreach ($nodeNames as $nodeName)
        {
            if (($node = $xpath->query(sprintf('.//%s', $nodeName), $relativeTo)->item(0)))
            {
                $values[$this->fetchPlainNodeName($nodeName)] = $castFunction($node->nodeValue);
            }
        }
        return $values;
    }

    // ---------------------------------- </HELPER METHODS> --------------------------------------
}
