<?php

use Honeybee\Core\Dat0r\Document;

class ImageFieldInputRenderer extends FieldInputRenderer
{
    protected function getWidgetType(Document $document)
    {
        return 'widget-asset-list';
    }

    protected function getWidgetOptions(Document $document)
    {
        $parentOptions = parent::getWidgetOptions($document);

        $fieldname = $this->getField()->getName();
        $assetIds = $document->getValue($fieldname);
        $texts = is_array($assetIds) ? $assetIds : array();
        $routing = AgaviContext::getInstance()->getRouting();
        $assetIds = empty($assetIds) ? array() : $assetIds;

        $assets = array();
        foreach (ProjectAssetService::getInstance()->multiGet($assetIds) as $id => $asset)
        {
            $metaData = $asset->getMetaData();
            $size = getimagesize($asset->getFullPath());
            $assets[] = array(
                'id' => $id,
                'url' => $routing->gen('asset.thumbnail', array('aid' => $id)),
                'name' => $asset->getFullName(),
                'caption' => isset($metaData['caption']) ? $metaData['caption'] : '',
                'copyright' => isset($metaData['copyright']) ? $metaData['copyright'] : '',
                'copyright_url' => isset($metaData['copyright_url']) ? $metaData['copyright_url'] : '',
                'aoi' => empty($metaData['aoi']) ? NULL : $metaData['aoi'],
                'width' => isset($size[0]) ? $size[0] : '',
                'height' => isset($size[0]) ? $size[1] : '',
                'mimeType' => $asset->getMimeType()
            );
        }

        return array_merge($parentOptions, array(
            'fieldname' => $this->generateInputName($document),
            'post_url' => htmlspecialchars_decode(urldecode($routing->gen('asset.update'))),
            'put_url' => htmlspecialchars_decode(urldecode($routing->gen('asset.put'))),
            'download_url' =>  htmlspecialchars_decode(urldecode($routing->gen('asset.binary', array('aid' => '{AID}')))),
            'assets' => $assets,
            'max' => isset($this->options['max_files']) ? (int)$this->options['max_files'] : 20,
            'allowed_types' => isset($this->options['allowed_types']) ? $this->options['allowed_types'] : array('image/png'),
            'aoi_url' => $routing->gen('common.service.detect_face'),
            'popover_pos' => isset($this->options['popover_pos']) ? $this->options['popover_pos'] : 'top'
        ));
    }

    protected function getTemplateName()
    {
        return 'PlainWidget.tpl.twig';
    }

    protected function generateInputName(Document $document)
    {
        return parent::generateInputName($document) . '[]';
    }
}
