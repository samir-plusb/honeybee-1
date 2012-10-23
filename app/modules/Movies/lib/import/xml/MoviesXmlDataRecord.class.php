<?php

/**
 * The MoviesXmlDataRecord class is a concrete implementation of the MoviesDataRecord base class.
 * It provides handling for mapping data coming from the xml import into the local movie-record format.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Movies
 * @subpackage      Import/Xml
 */
class MoviesXmlDataRecord extends MoviesDataRecord
{
    /**
     * Map the incoming movie data (array) to our masterRecord structure.
     *
     * @param string $data
     *
     * @return array
     */
    protected function parseData($data)
    {
        $mappedData = array(
            self::PROP_TITLE => $data['title'],
            self::PROP_TEASER => $data['teaser'],
            self::PROP_SUBLINE => $data['subline'],
            self::PROP_DIRECTOR => $data['director'],
            self::PROP_ACTORS => $data['actors'],
            self::PROP_RENTAL => $data['rental'],
            self::PROP_GENRE => $data['genre'],
            self::PROP_FSK => $data['fsk'],
            self::PROP_COUNTRY => isset($data['country']) ? $data['country'] : NULL,
            self::PROP_RELEASE_DATE => isset($data['release_date']) ? $data['release_date'] : NULL,
            self::PROP_DURATION => isset($data['duration']) ? $data['duration'] : NULL,
            self::PROP_YEAR => isset($data['year']) ? $data['year'] : NULL,
            self::PROP_WEBSITE => isset($data['website']) ? $data['website'] : NULL,
            self::PROP_SCREENINGS => isset($data['screenings']) ? $data['screenings'] : array(),
            self::PROP_MEDIA => $this->mapMedia(
                isset($data['media']) ? $data['media'] : array()
            ),
            self::PROP_SOURCE => 'movies-xml-import',
            self::PROP_IMPORT_IDENTIFIER => $this->buildImportIdentifier($data['id'])
        );

        return $mappedData;
    }

    protected function mapMedia(array $media)
    {
        $mediaData = array('trailers' => $media['trailers']);
        $assetKeys = array('scene', 'poster', 'poster600', 'hdPic');
        $galleryKeys = array('standard', 'big');

        foreach ($assetKeys as $key)
        {
            if (isset($media['images'][$key]))
            {
                if (($assetId = $this->aggregateAsset($media['images'][$key])))
                {
                    $mediaData['images'][$key] = $assetId;
                }
            }
        }
        foreach ($galleryKeys as $key)
        {
            if (isset($media['galleries'][$key]))
            {
                $mediaData['galleries'][$key] = $this->aggregateAssets($media['galleries'][$key]);
            }
        }
        return $mediaData;
    }

    protected function aggregateAssets(array $assets)
    {
        $assetIds = array();
        foreach ($assets as $asset)
        {
            if (($assetId = $this->aggregateAsset($asset)))
            {
                $assetIds[] = $assetId;
            }
        }
        return $assetIds;
    }

    protected function aggregateAsset(array $asset)
    {
        try
        {
            $baseDir = AgaviConfig::get('movies.telavision_dir');
            $imageUri = sprintf('file://%s/images/%s', $baseDir, $asset['src']);

            $metaData = array();
            $asset = ProjectAssetService::getInstance()->put($imageUri, $metaData, FALSE);
            $pictureId = $asset->getIdentifier();

            $metaData = $asset->getMetaData();
            $imagine = new Imagine\Gd\Imagine();
            $filePath = $asset->getFullPath();
            $image = $imagine->open($filePath);
            $size = $image->getSize();
            $assetData = array(
                'width' => $size->getWidth(),
                'height' => $size->getHeight(),
                'mime' => $asset->getMimeType(),
                'filename' => $asset->getFullName(),
                'modified' => date(DATE_ISO8601, filemtime($filePath)),
                'copyright' => 'Tel-A-Vision'
            );

            ProjectAssetService::getInstance()->update($asset, $assetData, FALSE);
        }
        catch(Exception $e)
        {
            echo $e->getMessage() . PHP_EOL;
            return NULL;
        }

        return $pictureId;
    }
}
