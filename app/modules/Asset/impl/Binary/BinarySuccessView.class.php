<?php

/**
 * The Asset_Binary_BinarySuccessView class handle the presentation logic for our Asset/Binary actions's success data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Mvc
 */
class Asset_Binary_BinarySuccessView extends AssetBaseView
{
    /**
     * Handle presentation logic for binary output.
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

        if (! $binary->fileExists())
        {
            $response->setHttpStatusCode(404);
        }
        else
        {
            $filePath = $binary->getPath();
            $lastModified = filemtime($filePath);
            $response->setHttpHeader('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified).' GMT');
            $response->setHttpHeader('Etag', $lastModified); // timestamp should be sufficient as ETag...

            $timestampFromRequest = $parameters->getHeader('If-Modified-Since', FALSE);
            if (
                (FALSE !== $timestampFromRequest && strtotime($timestampFromRequest) == $lastModified)
                || (trim($parameters->getHeader('If-None-Match', '') == $lastModified))
            )
            {
                $response->setHttpStatusCode(304);
            }
            else
            {
                if (is_readable($filePath))
                {
                    $response->setHttpHeader('Content-Type', $assetInfo->getMimeType());
                    $response->setHttpHeader('Content-Length', $assetInfo->getSize());
                    $response->setHttpHeader('Content-Disposition', 'attachment; filename='.$assetInfo->getFullName());
                    $response->setContent(fopen($filePath, 'rb'));
                }
                else
                {
                    $response->setHttpStatusCode(401);
                }
            }
        }
    }
}

?>