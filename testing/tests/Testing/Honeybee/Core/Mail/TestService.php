<?php

namespace Testing\Honeybee\Core\Mail;

use Honeybee\Core\Mail\IMail;

/**
 * Handles the sending of mails for a module.
 */
class TestService extends \Honeybee\Core\Mail\MailService
{
    protected $sent_mails = array();

    /**
     * Sends a mail via the configured mailer and transport.
     *
     * @param IMail $message mail to send
     * @param string $mailer_config_name name of the mailer config to get settings from
     *
     * @return array with the following keys: 'sent_mails' (number of mails sent) and 'failed_recipients' (email addresses that were not accepted by the internal transport)
     * 
     * @throws MessageConfigurationException in case of invalid message configurations
     */
    public function send(IMail $message, $mailer_config_name = null)
    {
        $this->sent_mails[] = $this->createSwiftMessage($message, $mailer_config_name);

        return array(
            self::SENT_MAILS => 1,
            self::FAILED_RECIPIENTS => array()
        );
    }
    
    /**
     * Initializes a \Swift_Mailer instance with a \Swift_NullTransport.
     *
     * @param string $mailer_name name of mailer to get settings for (if omitted, the settings of the default mailer are returned)
     */
    public function initSwiftMailer($mailer_config_name = null)
    {
        $settings = $this->getMailerSettings($mailer_config_name);

        $this->connection = \Swift_NullTransport::newInstance();
        $this->mailer = \Swift_Mailer::newInstance($this->connection);

        \Swift_Preferences::getInstance()->setCharset($settings->get('charset', 'utf-8'));
    }

    /**
     * @return \Swift_Message last message that was sent
     * 
     * @throws \Exception is no mails have been sent
     */
    public function getLastSentMail()
    {
        $cnt = count($this->sent_mails);

        if (0 === $cnt)
        {
            throw new \Exception('No mails have been sent.');
        }

        return $this->sent_mails[$cnt - 1];
    }

    /**
     * @return array of \Swift_Message instances that have been sent
     */
    public function getSentMails()
    {
        return $this->sent_mails;
    }
}

