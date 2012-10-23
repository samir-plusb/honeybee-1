<?php

class FilmZeitReviewsXmlParser implements IXmlParser
{
    const FILMZEIT_NAMESPACE_PREFIX = 'fz';

    /**
     * Holds a path to a xsd schema used to validate xml files before pulling data from them.
     *
     * @var string $schema
     */
    protected $schema;

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
     * Parse the given xml into an assoc. array.
     *
     * @param string $xmlSource
     *
     * @return array
     */
    public function parseXml($xmlSource)
    {
        $document = new DOMDocument('1.0', 'utf-8');
        if (is_file($xmlSource))
        {
            $document->load($xmlSource);
        }
        else
        {
            $document->loadXML($xmlSource);
        }

        $xpath = $this->createXpath($document, array(self::TBO_NAMESPACE_PREFIX));
        foreach ($xpath->query('//movie') as $movieNode)
        {
            var_dump($movieNode);
        }
    }
}


