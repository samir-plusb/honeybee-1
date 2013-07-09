<?php

namespace Honeybee\Core\Mail;

use Honeybee\Core\Config;
use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Service\IService;
use Honeybee\Core\Service\TemplateService;

/**
 * Handles mail templates for modules.
 */
class MailTemplateService implements IService
{

    /**
     * @var Honeybee\Core\Config\ArrayConfig with all mailers and the default mailer settings from mail.xml
     */
    protected $config;

    /**
     * @var Honeybee\Core\Dat0r\Module if the service was constructed with a module
     */
    protected $module;

    /**
     * @param mixed $mixed module to get mail config from or Config\ArrayConfig instance with mailers settings
     */
    public function __construct($mixed)
    {
        if ($mixed instanceof Module)
        {
            $this->module = $mixed;
            $config = new Config\AgaviXmlConfig(\AgaviConfig::get('core.modules_dir') . '/' . $this->module->getName() . '/config/mail.xml');
            $data = $config->toArray();
            $data['module_name'] = $this->module->getName();
            $this->config = new Config\ArrayConfig($data);
        }
        elseif ($mixed instanceof Config\ArrayConfig)
        {
            $this->config = $mixed;
        }
        elseif (is_array($mixed))
        {
            $this->config = new Config\ArrayConfig($mixed);
        }
        else
        {
            throw new \InvalidArgumentException('As PHP does not support overloading there is unfortunately no type hint for the correct type of constructor argument. Expected is a Honeybee Module or a mail ArrayConfig or even a compatible array with settings.');
        }
    }

    /**
     * This method constructs a Message from the given twig mail template.
     *
     * A valid twig mail template is a file with a '.mail.twig' extension,
     * that has multiple blocks with content:
     *
     * - 'subject' - subject of the message
     * - 'from' - email address of creator
     * - 'sender' - email address of sender (if different from creator)
     * - 'to' - email address of main recipient
     * - 'cc' - email address of carbon-copy receiver
     * - 'bcc' - email address of blind-carbon-copy receiver
     * - 'reply_to' - default email address for replies
     * - 'return_path' - email address to be used for bounce handling
     * - 'body_html' - HTML body part
     * - 'body_text' - plain text body part
     *
     * Only blocks, that exist in the template will be rendered and set.
     *
     * @param mixed $identifier usually the name of the template
     * @param array $variables array of placeholders for the twig template
     * @param array $options array of additional options for the renderer like 'template_extension' or 'add_agavi_assigns'
     *
     * @return \Honeybee\Core\Mail\Message mail message for further customization
     */
    public function createMessageFromTemplate($identifier, array $variables = array(), array $options = array())
    {
        if (!empty($this->module))
        {
            $template_service = $this->module->getService('template');
        }
        else
        {
            $template_service = new TemplateService($this->config);
        }

        if (!isset($options['template_extension']))
        {
            $options['template_extension'] = $this->config->get('template_extension', '.mail.twig');
        }

        if (!isset($options['add_agavi_assigns']))
        {
            $options['add_agavi_assigns'] = $this->config->get('add_agavi_assigns', true);
        }

        if (!$options['add_agavi_assigns'])
        {
            $twig_template = $template_service->loadTemplate($identifier, $options);
        }
        else
        {
            // add all assigns from the renderer parameters to the variables
            $layer = $template_service->getLayer($identifier, $options);
            $renderer = $layer->getRenderer();
            $context = \AgaviContext::getInstance();
            $assigns = array(
                'ac' => \AgaviConfig::toArray()
            );
            foreach ($renderer->getParameter('assigns', array()) as $item => $var)
            {
                $getter = 'get' . str_replace('_', '', $item);
                if (is_callable(array($context, $getter)))
                {
                    if (null === $var)
                    {
                        continue;
                    }
                    $assigns[$var] = call_user_func(array($context, $getter));
                }
            }
            $variables = array_merge($variables, $assigns);

            $twig_template = $renderer->loadTemplate($layer);
        }

        $message = new Message();
        if ($twig_template->hasBlock('subject'))
        {
            $message->setSubject($twig_template->renderBlock('subject', $variables));
        }

        if ($twig_template->hasBlock('body_html'))
        {
            $message->setBodyHtml($twig_template->renderBlock('body_html', $variables));
        }

        if ($twig_template->hasBlock('body_text'))
        {
            $message->setBodyText($twig_template->renderBlock('body_text', $variables));
        }

        if ($twig_template->hasBlock('from'))
        {
            $message->setFrom($twig_template->renderBlock('from', $variables));
        }

        if ($twig_template->hasBlock('to'))
        {
            $message->setTo($twig_template->renderBlock('to', $variables));
        }

        if ($twig_template->hasBlock('cc'))
        {
            $message->setCc($twig_template->renderBlock('cc', $variables));
        }

        if ($twig_template->hasBlock('bcc'))
        {
            $message->setBcc($twig_template->renderBlock('bcc', $variables));
        }

        if ($twig_template->hasBlock('return_path'))
        {
            $message->setReturnPath($twig_template->renderBlock('return_path', $variables));
        }

        if ($twig_template->hasBlock('sender'))
        {
            $message->setSender($twig_template->renderBlock('sender', $variables));
        }

        if ($twig_template->hasBlock('reply_to'))
        {
            $message->setReplyTo($twig_template->renderBlock('reply_to', $variables));
        }

        return $message;
    }

}
