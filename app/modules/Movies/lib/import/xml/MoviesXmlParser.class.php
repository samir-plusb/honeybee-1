<?php

/**
 * The MoviesXmlParser class is an abstract implementation of the IXmlParser interface.
 * We are not extending the BaseXmlParser class here, because we will want to refactor that one first,
 * before baking in the old interface all over the place.
 *
 * @version         $Id: MoviesXmlParser.class.php 1299 2012-06-12 16:09:14Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Movies
 * @subpackage      Import/Xml
 */
class MoviesXmlParser implements IXmlParser
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
        $data = array();
        foreach ($xpath->query('//FILM') as $movieNode)
        {
            $data[] = $this->transformMovieNode($movieNode);
        }
        return $data;
    }

    protected function transformMovieNode(DOMElement $movieNode)
    {
        $actorsNode = $movieNode->getElementsByTagName('DARSTELLER')->item(0);
        $actors = array();
        if ($actorsNode)
        {
            foreach (explode(',', $actorsNode->nodeValue) as $actor)
            {   
                $actors[] = trim($actor);
            }
        }
        
        $directorsNode = $movieNode->getElementsByTagName('REGIE')->item(0);
        $directors = array();
        if ($directorsNode)
        {
            foreach (explode(',', $directorsNode->nodeValue) as $director)
            {   
                $directors[] = trim($director);
            }
        }

        $rentalNode = $movieNode->getElementsByTagName('NEUSTART')->item(0);
        $genreNode = $movieNode->getElementsByTagName('GENRE')->item(0);
        $fskNode = $movieNode->getElementsByTagName('FSK')->item(0);
        $releaseNode = $movieNode->getElementsByTagName('NEUSTART')->item(0);
        $titelNode = $movieNode->getElementsByTagName('TITEL')->item(0);
        $teaserNode = $movieNode->getElementsByTagName('TEASER')->item(0);
        $yearNode = $movieNode->getElementsByTagName('JAHR')->item(0);
        $durationNode = $movieNode->getElementsByTagName('LAENGE')->item(0);
        $mediaNode = $movieNode->getElementsByTagName('BILDER')->item(0);
        $countryNode = $movieNode->getElementsByTagName('LAND')->item(0);

        return array(
            'id' => $movieNode->getAttribute('ID'),
            'title' => $titelNode->nodeValue,
            'teaser' => $teaserNode->nodeValue,
            'director' => $directors,
            'actors' => $actors,
            'rental' => $rentalNode ? $rentalNode->nodeValue : NULL,
            'genre' => $genreNode ? $genreNode->nodeValue : NULL,
            'fsk' => $fskNode ? $fskNode->nodeValue : NULL,
            'release_date' => $releaseNode ? $releaseNode->nodeValue : NULL,
            'year' => $yearNode ? $yearNode->nodeValue : NULL,
            'duration' => $durationNode ? $durationNode->nodeValue : NULL,
            'country' => $countryNode ? $countryNode->nodeValue : NULL,
            'media' => $mediaNode ? $this->transformMediaNode($mediaNode) : array()
        );
    }
    
    protected function transformMediaNode(DOMElement $mediaNode)
    {
        return array(
            'images' => $this->prepareImages($mediaNode),
            'galleries' => $this->prepareGalleries($mediaNode),
            'trailers' => $this->prepareTrailers($mediaNode)
        );
    }

    protected function prepareImages(DOMElement $mediaNode)
    {
        $images = array();
        $sceneImageNodes = $mediaNode->getElementsByTagName('SZENE');
        if (0 < $sceneImageNodes->length)
        {
            $sceneImageNode = $sceneImageNodes->item(0);
            $images['scene'] = array(
                'width' => $sceneImageNode->getAttribute('WIDTH'),
                'height' => $sceneImageNode->getAttribute('HEIGHT'),
                'src' => $sceneImageNode->nodeValue
            );
        }
        $posterImageNodes = $mediaNode->getElementsByTagName('PLAKAT');
        if (0 < $posterImageNodes->length)
        {
            $posterImageNode = $posterImageNodes->item(0);
            $images['poster'] = array(
                'width' => $posterImageNode->getAttribute('WIDTH'),
                'height' => $posterImageNode->getAttribute('HEIGHT'),
                'src' => $posterImageNode->nodeValue
            );
        }
        return $images;
    }

    protected function prepareGalleries(DOMElement $mediaNode)
    {
        $galleries = array();

        $galleryNodes = $mediaNode->getElementsByTagName('STRECKE');
        if (0 < $galleryNodes->length)
        {
            $galleries['standard'] = array();
            $galleryNode = $galleryNodes->item(0);
            foreach ($galleryNode->getElementsByTagName('BILD') as $imageNode)
            {
                $galleries['standard'][] = array(
                    'width' => $imageNode->getAttribute('WIDTH'),
                    'height' => $imageNode->getAttribute('HEIGHT'),
                    'src' => $imageNode->nodeValue
                );
            }
        }
        $galleryNodes = $mediaNode->getElementsByTagName('STRECKE_BIG');
        if (0 < $galleryNodes->length)
        {
            $galleries['big'] = array();
            $galleryNode = $galleryNodes->item(0);
            foreach ($galleryNode->getElementsByTagName('BILD') as $imageNode)
            {
                $galleries['big'][] = array(
                    'width' => $imageNode->getAttribute('WIDTH'),
                    'height' => $imageNode->getAttribute('HEIGHT'),
                    'src' => $imageNode->nodeValue
                );
            }
        }

        return $galleries;
    }

    protected function prepareTrailers(DOMElement $mediaNode)
    {
        $trailers = array();

        $trailerNodes = $mediaNode->getElementsByTagName('Trailer160');
        if (0 < $trailerNodes->length)
        {
            $trailers['res160'] = $trailerNodes->item(0)->nodeValue;
        }
        $trailerNodes = $mediaNode->getElementsByTagName('Trailer320');
        if (0 < $trailerNodes->length)
        {
            $trailers['res320'] = $trailerNodes->item(0)->nodeValue;
        }
        $trailerNodes = $mediaNode->getElementsByTagName('Trailer480');
        if (0 < $trailerNodes->length)
        {
            $trailers['res480'] = $trailerNodes->item(0)->nodeValue;
        }
        $trailerNodes = $mediaNode->getElementsByTagName('Trailer600');
        if (0 < $trailerNodes->length)
        {
            $trailers['res600'] = $trailerNodes->item(0)->nodeValue;
        }
        $trailerNodes = $mediaNode->getElementsByTagName('mobile');
        if (0 < $trailerNodes->length)
        {
            $trailers['mobile'] = $trailerNodes->item(0)->nodeValue;
        }

        return $trailers;
    }

    // ---------------------------------- <WORKING METHODS> --------------------------------------
}

?>
