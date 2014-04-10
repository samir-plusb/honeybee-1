<?php

namespace Honeybee\Agavi\View;

class EditSuccessView extends BaseView
{
    public function executeJson(\AgaviRequestDataHolder $requestData)
    {
        $document = $requestData->getParameter('document');
        $module_prefix = $this->getAttribute('module')->getOption('prefix');
        $routing = $this->getContext()->getRouting();

        $data = array(
            'state' => 'ok',
            'messages' => array($this->getContext()->getTranslationManager()->_('The document was saved successfully.', 'modules.labels')),
            'errors' => $this->getAttribute('errors', array()),
            'data' => $document->toArray()
        );

        if ('save_and_new' === $requestData->getParameter('save_type')) {
            $data['redirect_url'] = $routing->gen(sprintf('%s.edit', $module_prefix));
        } elseif ('save_and_close' === $requestData->getParameter('save_type')) {
            $list_setting_name = sprintf('%s_last_list_url', $module_prefix);
            $last_list_url = $this->getContext()->getUser()->getAttribute($list_setting_name, 'honeybee.list', false);
            if (!$last_list_url) {
                $last_list_url = $routing->gen(sprintf('%s.list', $module_prefix));
            }
            $data['redirect_url'] = $last_list_url;
        }

        $this->getResponse()->setContent(json_encode($data));
    }
}
