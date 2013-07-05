<?php

namespace Testing\Honeybee\Core\Mail;

use Testing\Honeybee\Core\BaseTest;

use Honeybee\Core\Mail;

class MessageTest extends BaseTest
{
    public function setUp()
    {
    }

    public function testValidEmails()
    {
        $this->assertTrue(Mail\Message::isValidEmail('user@example.com'), 'user@example.com');
        $this->assertTrue(Mail\Message::isValidEmail('user+folder@example.com'), 'user+folder@example.com');
        $this->assertTrue(Mail\Message::isValidEmail('someone@example.business'), 'someone@example.business');
        $this->assertTrue(Mail\Message::isValidEmail('new-asdf@trololo.co.uk'), 'new-asdf@trololo.co.uk');
        $this->assertTrue(Mail\Message::isValidEmail('omg@nsfw.xxx'), 'omg@nsfw.xxx');
        $this->assertTrue(Mail\Message::isValidEmail('A-Za-z0-9.!#$%&*+-/=?^_`{|}~@example.com'), 'A lot of special characters should be valid in the local part of email addresses');
        $this->assertTrue(Mail\Message::isValidEmail("o'hare@example.com"), "Single quotes are not working");
        $this->assertTrue(Mail\Message::isValidEmail("o'hare@xn--mller-kva.example"), "International domains should be supported via Punycode ACE strings");
        $this->assertTrue(Mail\Message::isValidEmail('user@example123example123example123example123example123example123456.com'), '63 characters long domain names should be valid');
        $this->assertTrue(Mail\Message::isValidEmail('user@example123example123example123example123example123example123456.co.nz'), '63 characters long domain names with top level domain "co.nz" should be valid');
        $this->assertTrue(Mail\Message::isValidEmail('example123example123example123example123example123example1234567@example.com'), '64 characters are valid according to SMTP in the local part');

        // this should be valid, but is not according to PHPs email filter:
        //$this->assertTrue(Mail\Message::isValidEmail('"foo bar"@example.com'), 'Spaces in email addresses should be allowed when they are in duoble quotes');
        //$this->assertTrue(Mail\Message::isValidEmail('user@localhost'), 'user@localhost');

        // TODO add other tests for length constraints - 320 octets overall, 64 for local part according to SMTP, 254 chars overall if you combine RFCs etc.
    }

    public function testInvalidEmails()
    {
        $this->assertFalse(Mail\Message::isValidEmail('müller@example.com'), 'Umlauts in the local part are not allowed');
        $this->assertFalse(Mail\Message::isValidEmail('umlaut@müller.com'), 'Umlauts etc. in the domain part should only be accepted punycode encoded');
        $this->assertFalse(Mail\Message::isValidEmail('trololo'), 'simple strings');
        $this->assertFalse(Mail\Message::isValidEmail(''), 'empty strings');
        $this->assertFalse(Mail\Message::isValidEmail(null), 'null');
        $this->assertFalse(Mail\Message::isValidEmail(false), 'boolean false');
        $this->assertFalse(Mail\Message::isValidEmail(true), 'boolean true');
        $this->assertFalse(Mail\Message::isValidEmail(array()), 'empty array');
        $this->assertFalse(Mail\Message::isValidEmail(new \stdClass()), 'standard class instance');
        $this->assertFalse(Mail\Message::isValidEmail('@'), 'single @ character');
        $this->assertFalse(Mail\Message::isValidEmail('a@b'), 'a@b');
        $this->assertFalse(Mail\Message::isValidEmail('<foo>@example.com'), 'Characters < and > are not valid in email addresses');
        $this->assertFalse(Mail\Message::isValidEmail('user@example123example123example123example123example123example1234567.com'), 'Domain names longer than 63 characters are invalid.');
        $this->assertFalse(Mail\Message::isValidEmail('example123example123example123example123example123example123example123example123example123example123example123example123example123example123example123example123example123example123456789012@example1example.example123example123example123example123example123.example123example123example123example123example123example123.com'), '320 octets/bytes are the maximum allowed length according to RFC 5322 and RFC 5321 valid emails');

        $this->assertFalse(Mail\Message::isValidEmail('Someone other <someone@example.com>'), 'Display names with email addresses may be valid, but are not support by us');
        $this->assertFalse(Mail\Message::isValidEmail('"Someone other" <someone@example.com>'), 'Quoted display names with email addresses may be valid, but are not support by us');

        // this should be invalid according to SMTP, but is not according to PHPs email filter:
        //$this->assertFalse(Mail\Message::isValidEmail('example123example123example123example123example123example1234567@example.com'), '64 characters are valid according to SMTP in the local part');
    }

    public function testAlwaysGetSingleSender()
    {
        $message = new Mail\Message();

        $message->setSender('sender@example.com');
        $this->assertEquals(array('sender@example.com' => null), $message->getSender(), "Simple email address as string does not work for setSender().");

        $message->setSender(array('sender@example.com', 'anotherone@example.com'));
        $this->assertEquals(array('sender@example.com' => null), $message->getSender(), "Only first of the two emails should have been set for setSender().");

        $message->setSender(array('sender@example.com'));
        $this->assertEquals(array('sender@example.com' => null), $message->getSender(), "Simple array with one email address should work with setSender().");
    }
    
    public function testAlwaysGetSingleReturnPath()
    {
        $message = new Mail\Message();

        $message->setReturnPath('return_path@example.com');
        $this->assertEquals(array('return_path@example.com' => null), $message->getReturnPath(), "Simple email address as string does not work for setReturnPath().");

        $message->setReturnPath(array('return_path@example.com', 'anotherone@example.com'));
        $this->assertEquals(array('return_path@example.com' => null), $message->getReturnPath(), "Only first of the two emails should have been set for setReturnPath().");

        $message->setReturnPath(array('return_path@example.com'));
        $this->assertEquals(array('return_path@example.com' => null), $message->getReturnPath(), "Simple array with one email address should work with setReturnPath().");
    }

    public function testReturnPathDefaultValueWorks()
    {
        $message = new Mail\Message();

        $this->assertEquals(array('return_path@example.com' => null), $message->getReturnPath('return_path@example.com'), "Simple default email address as string does not work for getReturnPath().");
        $this->assertEquals(array('return_path@example.com' => null), $message->getReturnPath(array('return_path@example.com', 'anotherone@example.com')), "Only first of the two default emails should have been set for getReturnPath().");
        $this->assertEquals(array('return_path@example.com' => null), $message->getReturnPath(array('return_path@example.com')), "Simple array with one default email address should work with getReturnPath().");
    }
    

    public function testFromDefaultValuesWorks()
    {
        $message = new Mail\Message();

        $this->assertEquals(array('from@example.com' => 'from'), $message->getFrom(array('from@example.com' => 'from')), "Simple default email address with display name as string does not work for getFrom().");
        $this->assertEquals(array('from@example.com' => null), $message->getFrom('from@example.com'), "Simple default email address as string does not work for getFrom().");
        $this->assertEquals(array('from@example.com' => null, 'anotherfrom@example.com' => null), $message->getFrom(array('from@example.com', 'anotherfrom@example.com')), "Both of the two default emails should have been set for getFrom().");
        $this->assertEquals(array('from@example.com' => null), $message->getFrom(array('from@example.com')), "Simple array with one default email address should work with getFrom().");
    }

    public function testToDefaultValuesWorks()
    {
        $message = new Mail\Message();

        $this->assertEquals(array('to@example.com' => 'from'), $message->getTo(array('to@example.com' => 'from')), "Simple default email address with display name as string does not work for getTo().");
        $this->assertEquals(array('to@example.com' => null), $message->getTo('to@example.com'), "Simple default email address as string does not work for getTo().");
        $this->assertEquals(array('to@example.com' => null, 'anotherto@example.com' => null), $message->getTo(array('to@example.com', 'anotherto@example.com')), "Both of the two default emails should have been set for getTo().");
        $this->assertEquals(array('to@example.com' => null), $message->getTo(array('to@example.com')), "Simple array with one default email address should work with getTo().");
    }

    public function testSettingToValuesWorks()
    {
        $message = new Mail\Message();

        $message->setTo(array('to@example.com' => 'to someone'));
        $this->assertEquals(array('to@example.com' => 'to someone'), $message->getTo('default@example.com'), "Simple email address with display name does not work for setTo().");

        $message->setTo('to@example.com');
        $this->assertEquals(array('to@example.com' => null), $message->getTo('default@example.com'), "Simple email address as string does not work for setTo().");

        $message->setTo(array('to@example.com'));
        $this->assertEquals(array('to@example.com' => null), $message->getTo(array('default@example.com')), "Simple array with one default email address should work with getTo().");

        $message->setTo(array('to@example.com' => 'to someone', 'anotherto@example.com'));
        $this->assertEquals(array('to@example.com' => 'to someone', 'anotherto@example.com' => null), $message->getTo(array('default@example.com', 'anotherdefault@example.com')), "Setting multiple addresses should have worked for getTo().");
    }
}