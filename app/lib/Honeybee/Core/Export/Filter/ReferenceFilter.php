<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\BaseDocument;

class ReferenceFilter extends BaseFilter
{
    public function execute(BaseDocument $document)
    {
        $filterOutput = array();

        $references = $this->getConfig()->get('references');

        foreach ($references as $reference => $fieldnames)
        {
            $filterOutput[$reference] = array();
            $referencedDocs = $document->getValue($reference);

            foreach ($referencedDocs as $refDocument)
            {
                $refData = array();

                foreach ($fieldnames as $fieldname => $exportKey)
                {
                    if ('shortIdentifier' === $fieldname)
                    {
                        $refData[$exportKey] = $refDocument->getShortIdentifier();
                    }
                    else
                    {
                        $field = $refDocument->getModule()->getField($fieldname);
                        $actsAsAssetsField = (bool)$field->getOption('acts_as_assets_field', FALSE);

                        if (TRUE === $actsAsAssetsField)
                        {
                            $assetsData = array();
                            foreach ($refDocument->getValue($fieldname) as $assetId)
                            {
                                $asset = \ProjectAssetService::getInstance()->get($assetId);
                                $assetsData[] = array(
                                    'size' => $asset->getSize(),
                                    'mime' => $asset->getMimeType(),
                                    'filename' => $asset->getFullName(),
                                    'copyright' => isset($metaData['copyright']) ? $metaData['copyright'] : '',
                                    'copyrightUrl' => isset($metaData['copyright_url']) ? $metaData['copyright_url'] : '',
                                    'caption' => isset($metaData['caption']) ? $metaData['caption'] : ''
                                );
                            }
                            $refData[$exportKey] = $assetsData;
                        }
                        else
                        {
                            $refData[$exportKey] = $refDocument->getValue($fieldname);
                        }
                    }
                }
                $filterOutput[$reference][] = $refData;
            }
        }

        return $filterOutput;
    }
}
