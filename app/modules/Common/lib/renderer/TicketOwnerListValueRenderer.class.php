<?php

use Dat0r\Core\Document\DocumentCollection;

class TicketOwnerListValueRenderer extends DefaultListValueRenderer
{
    public function renderValue($value, $field, array &$data = array())
    {
        if ($value instanceof DocumentCollection) {
            $value = $value->first()->getOwner();
        }

        return parent::renderValue($value, $field, $data);
    }

    public function renderTemplate(ListField $field, $options = array())
    {
        $user = AgaviContext::getInstance()->getUser();
        $loader = new Twig_Loader_Filesystem($this->getTemplateDirectory());
        $twig = new Twig_Environment($loader);

        $translation_domain = sprintf('%s.list', $this->module->getOption('prefix'));
        $translation_domain = isset($options['domain']) ? $options['domain'] : $translation_domain;

        $rendered = $twig->render(
            $this->getTemplateFilename(),
            array(
                'user' => $user,
                'field' => $field,
                'steal_locking_prompt' => AgaviContext::getInstance()->getTranslationManager()->_(
                    'steal_lock_prompt',
                    $translation_domain,
                    null
                )
            )
        );
        return $rendered;
    }

    protected function getTemplateDirectory()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR;
    }

    protected function getTemplateFilename()
    {
        return 'WorkflowState.tpl.twig';

    }
}

?>
