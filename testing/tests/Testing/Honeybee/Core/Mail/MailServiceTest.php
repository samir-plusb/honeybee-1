<?php

namespace Testing\Honeybee\Core\Mail;

use Testing\Honeybee\Core\BaseTest;
use Honeybee\Agavi\ConfigHandler\MailConfigHandler;

use Honeybee\Core\Mail;

class MailServiceTest extends BaseTest
{
    public function setUp()
    {
    }

    /**
     * @expectedException Honeybee\Core\Mail\MessageConfigurationException
     * @codeCoverageIgnore
     */
    public function testSendingEmptyMessageFails()
    {
        $message = new Mail\Message();
        $this->getMailService()->send($message);
    }

    /**
     * @expectedException Honeybee\Core\Mail\MessageConfigurationException
     * @codeCoverageIgnore
     */
    public function testSendingMessageWithoutRecipientsFails()
    {
        $message = new Mail\Message();
        $message->setFrom('test@test.com')->setSubject('subject')->setBodyText('some text');
        $this->getMailService()->send($message);
    }

    /**
     * @expectedException Honeybee\Core\Mail\MessageConfigurationException
     * @codeCoverageIgnore
     */
    public function testSendingMessageWithMultiplFromButWithoutSenderFails()
    {
        $message = new Mail\Message();
        $message->setFrom(array('test@test.com', 'foo@test.com'))->setTo('someone@test.com')->setSubject('subject')->setBodyText('some text');
        $this->getMailService()->send($message);
    }

    public function testSuccessfulMessageCreation()
    {
        $message = new Mail\Message();
        $message->setFrom('from@example.com')
                ->setTo('to@example.com')
                ->setSender('sender@example.com')
                ->setSubject('subject')
                ->setBodyText('plain text')
                ->setBodyHtml('<h1>HTML</h1>')
                ->setCc('cc@example.com')
                ->setBcc('bcc@example.com')
                ->setReplyTo('reply_to@example.com')
                ->setReturnPath('return_path@example.com');

        $mail_service = $this->getMailService();
        $mail_service->send($message);
        $swift_mail = $mail_service->getLastSentMail();

        $this->assertEquals(array('from@example.com' => null), $swift_mail->getFrom(), 'FROM is incorrect');
        $this->assertEquals(array('to@example.com' => null), $swift_mail->getTo(), 'TO is incorrect');
        $this->assertEquals(array('cc@example.com' => null), $swift_mail->getCc(), 'CC is incorrect');
        $this->assertEquals(array('bcc@example.com' => null), $swift_mail->getBcc(), 'BCC is incorrect');
        $this->assertEquals(array('reply_to@example.com' => null), $swift_mail->getReplyTo(), 'REPLY_TO is incorrect');
        $this->assertEquals(array('sender@example.com' => null), $swift_mail->getSender(), 'SENDER is incorrect');
        $this->assertEquals('return_path@example.com', $swift_mail->getReturnPath(), 'RETURN_PATH is incorrect');
        $this->assertEquals('subject', $swift_mail->getSubject(), 'SUBJECT is incorrect');

        $body_parts = $swift_mail->getChildren();
        $this->assertTrue(count($body_parts) === 2, 'There should be two body parts (html and plain text)');

        $content_types = array();
        $content = array();
        foreach ($body_parts as $part)
        {
            $content_types[] = $part->getContentType();
            $content[] = $part->getBody();
        }
        $this->assertContains('plain/text', $content_types, 'There should be a plain/text content type body part');
        $this->assertContains('text/html', $content_types, 'There should be a text/html content type body part');
        $this->assertContains('plain text', $content, 'There should be a plain text body part');
        $this->assertContains('<h1>HTML</h1>', $content, 'There should be a html body part');
    }

    public function testSuccessfulMessageCreationWithOverrideAllRecipients()
    {
        $default_settings = array(
            'charset' => 'utf8',
            'override_all_recipients' => 'override_all_recipients@example.com'
        );

        $config = array(
            MailConfigHandler::KEY_DEFAULT_MAILER => $default_settings,
            MailConfigHandler::KEY_MAILERS => array(
                'default' => $default_settings
            )
        );
        $message = new Mail\Message();
        $message->setFrom('from@example.com')
                ->setTo('to@example.com')
                ->setCc('cc@example.com')
                ->setBcc('bcc@example.com')
                ->setSubject('subject')
                ->setBodyText('plain text')
                ->setBodyHtml('<h1>HTML</h1>')
                ->setSender('sender@example.com')
                ->setReplyTo('reply_to@example.com')
                ->setReturnPath('return_path@example.com');

        $mail_service = $this->getMailService($config);
        $mail_service->send($message);
        $swift_mail = $mail_service->getLastSentMail();

        // the important test
        $this->assertEquals(array('override_all_recipients@example.com' => null), $swift_mail->getTo(), 'TO is incorrect');
        $this->assertEquals(array('override_all_recipients@example.com' => null), $swift_mail->getCc(), 'CC is incorrect');
        $this->assertEquals(array('override_all_recipients@example.com' => null), $swift_mail->getBcc(), 'BCC is incorrect');

        // the rest should not be overridden
        $this->assertEquals(array('from@example.com' => null), $swift_mail->getFrom(), 'FROM is incorrect');
        $this->assertEquals(array('reply_to@example.com' => null), $swift_mail->getReplyTo(), 'REPLY_TO is incorrect');
        $this->assertEquals(array('sender@example.com' => null), $swift_mail->getSender(), 'SENDER is incorrect');
        $this->assertEquals('return_path@example.com', $swift_mail->getReturnPath(), 'RETURN_PATH is incorrect');
        $this->assertEquals('subject', $swift_mail->getSubject(), 'SUBJECT is incorrect');

        $body_parts = $swift_mail->getChildren();
        $this->assertTrue(count($body_parts) === 2, 'There should be two body parts (html and plain text)');

        $content_types = array();
        $content = array();
        foreach ($body_parts as $part)
        {
            $content_types[] = $part->getContentType();
            $content[] = $part->getBody();
        }
        $content = implode('', $content);

        $this->assertContains('plain/text', $content_types, 'There should be a plain/text content type body part');
        $this->assertContains('text/html', $content_types, 'There should be a text/html content type body part');
        $this->assertContains('plain text', $content, 'Could not find plain text in the body content');
        $this->assertContains('HTML', $content, 'Could not find HTML in the body content');
    }

    public function testSuccessfulMessageCreationWithOverrideAllRecipientsForToFieldOnly()
    {
        $config = array(
            MailConfigHandler::KEY_DEFAULT_MAILER => array(
                'override_all_recipients' => 'override_all_recipients@example.com'
            ),
            MailConfigHandler::KEY_MAILERS => array(
                'default' => array(
                    'override_all_recipients' => 'another_override_all_recipients@example.com'
                )
            )
        );
        $message = new Mail\Message();
        $message->setFrom('from@example.com')
                ->setTo('to@example.com')
                ->setSubject('subject');

        $mail_service = $this->getMailService($config);
        $mail_service->send($message);
        $swift_mail = $mail_service->getLastSentMail();

        // the important test
        $this->assertEquals(array('override_all_recipients@example.com' => null), $swift_mail->getTo(), 'TO is incorrect');
        $this->assertEmpty($swift_mail->getCc(), 'CC should be null');
        $this->assertEmpty($swift_mail->getBcc(), 'BCC should be null');

        // the rest should not be overridden
        $this->assertEquals(array('from@example.com' => null), $swift_mail->getFrom(), 'FROM is incorrect');
        $this->assertEquals('subject', $swift_mail->getSubject(), 'SUBJECT is incorrect');
    }

    public function testSuccessfulMessageCreationWithDefaultsFromConfig()
    {
        $default_settings = array(
            'charset' => 'utf7',
            'default_subject' => 'default subject',
            'address_defaults' => array(
                'from' => 'default_from@example.com',
                'to' => 'default_to@example.com',
                'bcc' => 'default_bcc@example.com'
            ),
            'address_overrides' => array(
                'sender' => 'sender_override@example.com',
                'return_path' => 'return_path_override@example.com'
            )
        );

        $config = array(
            MailConfigHandler::KEY_DEFAULT_MAILER => $default_settings,
            MailConfigHandler::KEY_MAILERS => array(
                'default' => $default_settings
            )
        );
        $message = new Mail\Message();
        $message->setSender('will-be-overwritten@example.com')->setReturnPath('and-this-one-too@example.com');

        $mail_service = $this->getMailService($config);
        $mail_service->send($message);
        $swift_mail = $mail_service->getLastSentMail();

        $this->assertEquals('utf7', $swift_mail->getCharset(), 'Charset was not overridden');
        $this->assertEquals(array('default_from@example.com' => null), $swift_mail->getFrom(), 'FROM is incorrect');
        $this->assertEquals(array('default_to@example.com' => null), $swift_mail->getTo(), 'TO is incorrect');
        $this->assertEquals(array('default_bcc@example.com' => null), $swift_mail->getBcc(), 'BCC is incorrect');
        $this->assertEquals(array('sender_override@example.com' => null), $swift_mail->getSender(), 'SENDER was not overridden');
        $this->assertEquals('return_path_override@example.com', $swift_mail->getReturnPath(), 'RETURN_PATH was not overridden');
    }

    public function testSuccessfulMessageCreationWithCustomMailerConfig()
    {
        $config = array(
            MailConfigHandler::KEY_DEFAULT_MAILER => array(
                'default_subject' => 'default subject',
            ),
            MailConfigHandler::KEY_MAILERS => array(
                'default' => array(
                    'default_subject' => 'default subject',
                ),
                'trololo' => array(
                    'default_subject' => 'trololo subject'
                )
            )
        );
        $message = new Mail\Message();
        $message->setSender('sender@example.com')->setTo('to@example.com');

        $mail_service = $this->getMailService($config);
        $mail_service->send($message, 'trololo');
        $swift_mail = $mail_service->getLastSentMail();

        $this->assertEquals('trololo subject', $swift_mail->getSubject(), 'Subject was not taken from custom mailer config');
    }

    /**
     * @param mixed $config array or Config\ArrayConfig instance with mailer settings
     * 
     * @return \Testing\Honeybee\Core\Mail\TestService
     */
    protected function getMailService($config = array())
    {
        return new TestService($config);
    }
}