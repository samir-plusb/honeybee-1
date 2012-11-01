<?php

/**
 * The ScreeningXmlParser class is an abstract implementation of the IXmlParser interface.
 * We are not extending the BaseXmlParser class here, because we will want to refactor that one first,
 * before baking in the old interface all over the place.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Movies
 * @subpackage      Import/Screening
 */
class ScreeningXmlParser implements IXmlParser
{
    // ---------------------------------- <IXmlParser IMPL> --------------------------------------

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
        return $this->processDocument($document);
    }

    // ---------------------------------- </IXmlParser IMPL> -------------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    protected function processDocument(DOMDocument $document)
    {
        $xpath = new DOMXPath($document);
        // collect all screenings grouped by movie
        $screeningsByMovie = array();
        foreach ($xpath->query('//TAG') as $screeningNode)
        {
            $screening = $this->transformScreeningNode($screeningNode);
            $movieId = $screening['movieId'];
            unset($screening['movieId']);
            if (! isset($screeningsByMovie[$movieId]))
            {
                $screeningsByMovie[$movieId] = array();
            }
            $screeningsByMovie[$movieId][] = $screening;
        }
        // then re-structure for return
        $data = array();
        foreach ($screeningsByMovie as $movieId => $screenings)
        {
            $data[] = array('movieId' => $movieId, 'screenings' => $screenings);
        }
        return $data;
    }

    protected function transformScreeningNode(DOMElement $screeningNode)
    {
        $times = array();
        foreach ($screeningNode->getElementsByTagName('UHRZEIT') as $timeNode)
        {
            $times[] = $timeNode->nodeValue;
        }
        $data = array(
            'movieId' => $screeningNode->getAttribute('FILMID'),
            'theaterId' => $screeningNode->getAttribute('KINOID'),
            'screenId' => $screeningNode->getAttribute('LEINWANDID'),
            'version' => $screeningNode->getAttribute('FASSUNGID'),
            'date' => $screeningNode->getAttribute('DATUM'),
            'times' => $times
        );
        return $data;
    }

    // ---------------------------------- <WORKING METHODS> --------------------------------------
}

?>
