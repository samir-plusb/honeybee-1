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

        if (! $binary->fileExists())
        {
            $response->setHttpStatusCode(404);
        }
        else if(FALSE === strpos($assetInfo->getMimeType(), 'application/pdf'))
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
        else
        {
            $filePath = AgaviConfig::get('core.app_dir') . '/../pub/static/deploy/_global/binaries/pdficon_large.png';
        }

        if (is_readable($filePath))
        {
            $response->setHttpHeader('Content-Type', 'image/png');
            $response->setHttpHeader('Content-Length', filesize($filePath));
            $response->setHttpHeader('Content-Disposition', 'inline; filename='.$assetInfo->getFullName());
            $response->setContent(fopen($filePath, 'rb'));
        }
        else
        {
            $response->setHttpStatusCode(401);
        }
    }
}
