<?php

namespace Honeybee\Core\Mail;

use Honeybee\Agavi\ConfigHandler\MailConfigHandler;
use Honeybee\Agavi\Logging;
use Honeybee\Core\Config;
use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Mail\MessageConfigurationException;
use Honeybee\Core\Service\IService;
use Honeybee\Core\Mail\MailTemplateService;

/**
 * Handles the sending of mails for a module.
 */
class MailService implements IService, IMailer
{
    /**
     * Key for 'number of sent mails' in return array of the send method.
     */
    const SENT_MAILS = 'sent_mails';

    /**
     * Key for 'failed recipient addresses' in return array of the send method.
     */
    const FAILED_RECIPIENTS = 'failed_recipients';

    /**
     * @var Honeybee\Core\Config\ArrayConfig with all mailers and the default mailer settings from mail.xml
     */
    protected $mailer_configs;

    /**
     * @var \Swift_SendmailTransport
     */
    protected $connection = null;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer = null;

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
            $this->mailer_configs = new Config\ArrayConfig($data);

        }
        elseif ($mixed instanceof Config\ArrayConfig)
        {
            $this->mailer_configs = $mixed;
        }
        elseif (is_array($mixed))
        {
            $this->mailer_configs = new Config\ArrayConfig($mixed);
        }
        else
        {
            throw new \InvalidArgumentException('As PHP does not support overloading there is unfortunately no type hint for the correct type of constructor argument. Expected is a Honeybee Module or a mail ArrayConfig or even a compatible array with settings.');
        }

        $this->initSwiftMailer();
    }

    /**
     * Initializes a \Swift_Mailer instance with a transport and
     * sets the default charset.
     *
     * @param string $mailer_name name of mailer to get settings for (if omitted, the settings of the default mailer are used)
     */
    protected function initSwiftMailer($mailer_config_name = null)
    {
        $settings = $this->getMailerSettings($mailer_config_name);
        $class = $settings->get('swift_transport_class', '\\Swift_SendmailTransport');

        if ($class == '\\Swift_SmtpTransport') {

            $host = $settings->get('host', 'localhost');
            $username = $settings->get('username', null);
            $password = $settings->get('password', null);

            $this->connection = $class::newInstance($host)
                ->setUsername($username)
                ->setPassword($password);
        } else {
            $this->connection = $class::newInstance();
        }

        $this->mailer = \Swift_Mailer::newInstance($this->connection);

        \Swift_Preferences::getInstance()->setCharset($settings->get('charset', 'utf-8'));

        // to enable logging of communication and sent messages
        $this->logger = new \Swift_Plugins_Loggers_ArrayLogger();
        $this->message_logger = new \Swift_Plugins_MessageLogger();
        $this->mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($this->logger));
        $this->mailer->registerPlugin($this->message_logger);
    }

    /**
     * Returns the settings of the given mailer.
     * 
     * @param string $mailer_name name of mailer to get settings for (if omitted, the settings of the default mailer are returned)
     * 
     * @return Config\ArrayConfig with settings for the given mailer name (or the default mailer)
     */
    public function getMailerSettings($mailer_name = null)
    {
        if (null === $mailer_name)
        {
            return new Config\ArrayConfig($this->mailer_configs->get(MailConfigHandler::KEY_DEFAULT_MAILER, array()));
        }

        $all_mailers = $this->mailer_configs->get(MailConfigHandler::KEY_MAILERS, array());
        if (!isset($all_mailers[$mailer_name]))
        {
            throw new \InvalidArgumentException("There is no mailer with the name: $mailer_name");
        }

        return new Config\ArrayConfig($all_mailers[$mailer_name]);
    }

    /**
     * Returns the internally used mailer instance to allow for more advanced
     * use cases where the simple IMail interface is not sufficient at all.
     *
     * @return \Swift_Mailer instance used internally for mailing
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * Sends a mail via the configured mailer and transport of the module.
     *
     * @param IMail $message mail to send
     * @param string $mailer_config_name name of the mailer config to get settings from
     *
     * @return array with the following keys: 'sent_mails' (number of mails sent) and 'failed_recipients' (email addresses that were not accepted by the internal transport)
     * 
     * @throws MessageConfigurationException in case of invalid message configurations
     * @throws \InvalidArgumentException in case of unknown mailer name from config
     */
    public function send(IMail $message, $mailer_config_name = null)
    {
        $settings = $this->getMailerSettings($mailer_config_name);

        $mail = $this->createSwiftMessage($message, $mailer_config_name);

        $failed_recipients = array();
        $sent_mails = $this->mailer->send($mail, $failed_recipients);

        if (false !== $settings->get('logging_enabled', false))
        {
            $logger_name = $settings->get('logger_name', 'mail');
            $logger_manager = \AgaviContext::getInstance()->getLoggerManager();
            $logger_manager->logTo($logger_name, Logging\Logger::INFO, __METHOD__, $this->logger->dump());
            if (false !== $settings->get('log_messages', false))
            {
                foreach ($this->message_logger->getMessages() as $message)
                {
                    $logger_manager->logTo($logger_name, Logging\Logger::INFO, 'EMAIL', $message);
                }
            }
        }

        return array(
            self::SENT_MAILS => $sent_mails,
            self::FAILED_RECIPIENTS => $failed_recipients
        );
    }

    /**
     * Convenience proxy method for the MailTemplateService that
     * creates a new message instance from a twig mail template.
     *
     * @param mixed $identifier template name
     * @param array $variables array of placeholders for the twig template
     * @param array $options array of additional options for the renderer like 'template_extension' or 'add_agavi_assigns'
     *
     * @return Message instance to customize further
     */
    public function createMessageFromTemplate($identifier, array $variables = array(), array $options = array())
    {
        if (!empty($this->module))
        {
            $mail_template_service = $this->module->getService('mail-template');
        }
        else
        {
            $mail_template_service = new MailTemplateService($this->mailer_configs);
        }

        return $mail_template_service->createMessageFromTemplate($identifier, $variables, $options);
    }

    /**
     * Create Swift_Message instance from the given IMail instance.
     * 
     * @param IMail $message
     * @param string $mailer_config_name name of the mailer config to get settings from
     *
     * @return \Swift_Message instance
     *
     * @throws MessageConfigurationException in case of misconfigured message
     * @throws \InvalidArgumentException in case of unknown mailer name from config
     */
    public function createSwiftMessage(IMail $message, $mailer_config_name = null)
    {
        $settings = $this->getMailerSettings($mailer_config_name);
        $message_defaults = new Config\ArrayConfig($settings->get('address_defaults', array()));
        $message_overrides = new Config\ArrayConfig($settings->get('address_overrides', array()));

        \Swift_Preferences::getInstance()->setCharset($settings->get('charset', 'utf-8'));

        $mail = \Swift_Message::newInstance();

        $mail->setSubject($message->getSubject($settings->get('default_subject')));

        $from = $message_overrides->get('from', $message->getFrom($message_defaults->get('from')));
        if (!empty($from))
        {
            $mail->setFrom($from);
        }

        $sender = $message_overrides->get('sender', $message->getSender($message_defaults->get('sender')));
        if (!empty($sender))
        {
            $mail->setSender($sender);
        }

        // sender is mandatory if multiple from addresses are set
        if (is_array($from) && count($from) > 1 && empty($sender))
        {
            throw new MessageConfigurationException('A single "sender" email address must be specified when multiple "from" email addresses are set.');
        }

        // we need at least a sender or a from to be honest citizens
        if (empty($from) && empty($sender))
        {
            throw new MessageConfigurationException('Either "from" or "sender" must be set with a valid email address on a message. Usually "from" is considered to be mandatory with the "sender" being optional to distinguish between writers of an email and its actual sender.');
        }

        $reply_to = $message_overrides->get('reply_to', $message->getReplyTo($message_defaults->get('reply_to')));
        if (!empty($reply_to))
        {
            $mail->setReplyTo($reply_to);
        }

        $return_path = $message_overrides->get('return_path', $message->getReturnPath($message_defaults->get('return_path')));
        if (!empty($return_path))
        {
            // Swift only wants a string as email on the return path (despite the behaviour on other address fields)
            if (is_array($return_path))
            {
                $return_path = array_keys($return_path);
                $return_path = array_shift($return_path);
            }
            $mail->setReturnPath($return_path);
        }

        $date = $message->getDate($message_defaults->get('date'));
        if ($settings->has('default_date'))
        {
            $date = strtotime($settings->get('default_date'));
        }
        if (!empty($date) && is_int($date))
        {
            $mail->setDate($date);
        }

        $to = $message_overrides->get('to', $message->getTo($message_defaults->get('to')));
        if (!empty($to))
        {
            $mail->setTo($to);
        }

        $cc = $message_overrides->get('cc', $message->getCc($message_defaults->get('cc')));
        if (!empty($cc))
        {
            $mail->setCc($cc);
        }

        $bcc = $message_overrides->get('bcc', $message->getBcc($message_defaults->get('bcc')));
        if (!empty($bcc))
        {
            $mail->setBcc($bcc);
        }

        $body_html = $message->getBodyHtml($message_defaults->get('default_body_html'));
        if (!empty($body_html))
        {
            $mail->addPart($body_html, "text/html");
        }

        $body_text = $message->getBodyText($message_defaults->get('default_body_text'));
        if (!empty($body_text))
        {
            $mail->addPart($body_text, "text/plain");
        }

        $attachments = $message->getAttachments();
        if (!empty($attachments))
        {
            foreach ($attachments as $attachment)
            {
                if (!is_array($attachment))
                {
                    continue;
                }

                $mail->attach($this->createSwiftAttachment($attachment));
            }
        }

        // if to, cc or bcc are set we have to override them if a setting is set
        $override_all_recipients = $settings->get('override_all_recipients', false);
        if (false !== $override_all_recipients && !empty($override_all_recipients))
        {
            if (!empty($to))
            {
                $to = $override_all_recipients; // for later validation
                $mail->setTo($to);
            }

            if (!empty($cc))
            {
                $cc = $override_all_recipients; // for later validation
                $mail->setCc($cc);
            }

            if (!empty($bcc))
            {
                $bcc = $override_all_recipients; // for later validation
                $mail->setBcc($bcc);
            }
        }

        // we won't send mails without recipients
        if (empty($to) && empty($cc) && empty($bcc))
        {
            throw new MessageConfigurationException('No recipients are set for this email. Set "to", "cc" and/or "bcc" email addresses on the message.');
        }

        // do not allow to long text lines
        if ($settings->has('max_line_length'))
        {
            $mail->setMaxLineLength((int) $settings->get('max_line_length', 78));
        }

        // add X-Priority header
        if ($settings->has('priority'))
        {
            $mail->setPriority((int) $settings->get('priority', 3));
        }

        // request read receipts if necessary
        if ($settings->has('read_receipt_to'))
        {
            $mail->setReadReceiptTo($settings->get('read_receipt_to'));
        }

        return $mail;
    }

    /**
     * @param array $attachment information about the attachment to create
     *
     * @return \Swift_Attachment or \Swift_EmbeddedFile
     */
    protected function createSwiftAttachment($attachment)
    {
        $file = '';
        if (IMail::CONTENT_DISPOSITION_INLINE === $attachment['content_disposition'])
        {
            if (!empty($attachment['path']))
            {
                $file = \Swift_EmbeddedFile::fromPath($attachment['path']);
                $file->setFilename($attachment['name']);
                $file->setContentType($attachment['content_type']);
            }
            else
            {
                $file = \Swift_EmbeddedFile::newInstance($attachment['content'], $attachment['name'], $attachment['content_type']);
            }
        }
        elseif (IMail::CONTENT_DISPOSITION_ATTACHMENT === $attachment['content_disposition'])
        {
            if (!empty($attachment['path']))
            {
                $file = \Swift_Attachment::fromPath($attachment['path']);
                $file->setFilename($attachment['name']);
                $file->setContentType($attachment['content_type']);
            }
            else
            {
                $file = \Swift_Attachment::newInstance($attachment['content'], $attachment['name'], $attachment['content_type']);
            }
        }
        else
        {
            throw new MessageConfigurationException('Could not use given attachment ' . print_r($attachment, true) . ' in ' . __METHOD__ . '. Use correct array or path instead.');
        }

        return $file;
    }
}
