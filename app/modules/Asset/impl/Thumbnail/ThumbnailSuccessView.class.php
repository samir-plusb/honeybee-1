<?php

/**
 * The Asset_Thumbnail_ThumbnailSuccessView class handle the presentation logic for our Asset/Thumbnail actions's success data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Mvc
 */
class Asset_Thumbnail_ThumbnailSuccessView extends AssetBaseView
{
    /**
     * Handle presentation logic for the web  (html).
     * 
     * @param       AgaviRequestDataHolder $parameters 
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeBinary(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $assetInfo = $this->getAttribute('asset_info');



        $response = $this->getContainer()->getResponse();
        $binary = new AssetFile($assetInfo->getIdentifier());
        $filePath = $binary->getPath();
        $isPdfFile = FALSE !== strpos($assetInfo->getMimeType(), 'application/pdf');
        $isAudioFile = FALSE !== strpos($assetInfo->getMimeType(), 'audio');

        if (! $binary->fileExists())
        {
            $response->setHttpStatusCode(404);
        }
        else if(! $isPdfFile && ! $isAudioFile)
        {
            $lastModified = filemtime($filePath);
            $response->setHttpHeader('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified).' GMT');
            $response->setHttpHeader('Etag', $lastModified); // timestamp should be sufficient as ETag...

            $timestampFromRequest = $parameters->getHeader('If-Modified-Since', FALSE);
            if (
                (FALSE !== $timestampFromRequest && strtotime($timestampFromRequest) == $lastModified)
                || (trim($parameters->getHeader('If-None-Match', '') == $lastModified))
            )
            {
                $filePath = NULL;
            }
        }
        else if (! $isAudioFile)
        {
            $filePath = AgaviConfig::get('core.app_dir') . '/../pub/static/deploy/_global/binaries/pdficon_large.png';
        }
        else
        {
            $filePath = AgaviConfig::get('core.app_dir') . '/../pub/static/deploy/_global/binaries/audio.png';
        }

        $sanitized_filename = preg_replace('/(\w*[\x80-\xFF]+\w*)/', '', $assetInfo->getName());

        $extension = $assetInfo->getExtension();
        if (!empty($extension)) {
            $sanitized_filename .= '.' . $extension;
        }

        if (mb_strlen($sanitized_filename) > 74) {
            $sanitized_filename = mb_substr($sanitized_filename, -74);
        }

        if (is_readable($filePath)) {
            $response->setHttpHeader('Content-Type', $assetInfo->getMimeType());
            $response->setHttpHeader('Content-Length', filesize($filePath));
            $response->setHttpHeader('Content-Disposition', 'inline; filename="' . $sanitized_filename . '"');
            $response->setContent(fopen($filePath, 'rb'));
        } else {
            $response->setHttpStatusCode(401);
        }
    }
}
