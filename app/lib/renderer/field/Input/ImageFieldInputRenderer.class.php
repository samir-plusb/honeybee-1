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
                'url' => $routing->gen('asset.binary', array('aid' => $id)),
                'name' => $asset->getFullName(),
                'caption' => isset($metaData['caption']) ? $metaData['caption'] : '',
                'copyright' => isset($metaData['copyright']) ? $metaData['copyright'] : '',
                'copyright_url' => isset($metaData['copyright_url']) ? $metaData['copyright_url'] : '',
                'aoi' => empty($metaData['aoi']) ? NULL : $metaData['aoi'],
                'width' => isset($size[0]) ? $size[0] : '',
                'height' => isset($size[0]) ? $size[1] : ''
            );
        }

        return array(
            'autobind' => TRUE,
            'fieldname' => $this->generateInputName($document),
            'post_url' => htmlspecialchars_decode(urldecode($routing->gen('asset.update'))),
            'put_url' => htmlspecialchars_decode(urldecode($routing->gen('asset.put'))),
            'assets' => $assets,
            'aoi_url' => $routing->gen('common.service.detect_face'),
            'popover_pos' => isset($this->options['popover_pos']) ? $this->options['popover_pos'] : 'top'
        );
    }

    protected function getTemplateName()
    {
        return 'PlainWidget.tpl.php';
    }

    protected function generateInputName(Document $document)
    {
        return parent::generateInputName($document) . '[]';
    }
}
