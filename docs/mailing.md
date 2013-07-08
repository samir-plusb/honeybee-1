# Mailing

- [Mailing](#mailing)
  - [Usage examples](#usage-examples)
    - [Email addresses](#email-addresses)
  - [Configuration](#configuration)
    - [Settings](#settings)
  - [Using custom mailer settings](#using-custom-mailer-settings)
  - [Transport modification](#transport-modification)
  - [Support for other mailing libraries](#support-for-other-mailing-libraries)
  - [TBD / Ideas / Misc](#tbd--ideas--misc)

To send emails you usually have to get a `Honeybee\Core\Mail\Service` instance
from a Honeybee module. That service has a `send()` method that accepts a
`Honeybee\Core\Mail\IMail` implementing message. There is a class holding
mails called `Honeybee\Core\Mail\Message` that eases the creation of mails.

By default the mail service uses the `SwiftMailer` library to create mails and
sends them via the ```\Swift_SendmailTransport```. Email fields like `To`, `Cc`
and others may be configured with default values or even be overridden on a per
module basis. More information about the configuration can be found further
down.

# Usage examples

If you are in an Agavi action or view you may just get the mail service from a
Honeybee module and send a created message like this:

```php
$mail = Message::create('from@example.com', 'to@example.com', 'Subject', '<h1>HTML-Body-Part</h1>', 'Text body part');
$info = $mail_service->send($mail);
```

To create a Honeybee mail message you instantiate a `Honeybee\Core\Mail\Message`
and set the fields you like. The following creates a text only email that has
two recipients and a return path set for bounce handling:

```php
$mail = new Message();
$mail->setFrom('from@example.com')
     ->setTo(array('to@example.com', 'another-to@example.com'))
     ->setBodyText('Some plain text body')
     ->setReturnPath('bounces@example.com');
```

You can create emails that have multiple `From` addresses when you set a
`Sender` address to specify who really sent the message:

```php
$mail = new Message();
$mail->setFrom(array('boss-1@example.com', 'boss-2@example.com'))
     ->setSender('system@example.com')
     ->setTo(array('recipient@example.com', 'another-recipient@example.com'))
     ->setBodyText('Some important mail from the bosses sent from system')
     ->setReplyTo('marketing-department@example.com')
     ->setReturnPath('bounces@example.com');
```

To add attachments you do one of the following:

```php
$mail = new Message();

// add a local file as an attachment with a name and content type
$mail->addFile('/path/to/1234.pdf', 'receipt.pdf', 'application/pdf');

// add content as an inline attachment under given name and content type
$mail->addAttachment($data_in_memory, 'receipt.pdf', 'application/pdf', Message::CONTENT_DISPOSITION_INLINE);
```

If you don't specify a file name, the basename of the given file is used. The
default content type is ```application/octet-stream``` when you omit that
parameter. There are two constants defined on the `Honeybee\Core\Mail\Message`
that should be used for the content disposition parameter:
```CONTENT_DISPOSITION_INLINE``` and ```CONTENT_DISPOSITION_ATTACHMENT```. The
content disposition attachment is the default and leads to normal attachments as
everyone knows them while the inline disposition type should be presented by
mail clients as an inline element (e.g. image under a text body). Remember, that
this is dependent on the support of email clients.

## Email addresses

The `Mail\Message` has a static method ```isValidEmail($address)``` that you may
use to check the validity of email addresses according to the system's rules.
When you try to set fields on the email like `From`, `To` etc. the given
addresses are validated using that method. If you try to set parameters, that
are invalid a `MessageConfigurationException` is thrown. The method uses the
PHP internal ```FILTER_VALIDATE_EMAIL``` with the ```filter_var``` method.

The emails in the `Mail\Message` are consolidated into a SwiftMailer compatible
array format with the key being the email address and the value being the
display name. If no display name is given or supported (as for `Return-Path`)
the display name will be `null`.

```php
$mail = new Message();

$mail->setFrom('simple@example.com');
$from = $mail->getFrom(); // gives: array('simple@example.com' => null)

$mail->setFrom(array('simple@example.com' => 'From Someone'));
$from = $mail->getFrom(); // gives: array('simple@example.com' => 'From Someone')

```

Each of the field getters can take a default value to be returned when the value
is missing. The returned default value will be consolidated in the same array
format as the normal setters do. The default value will just be returned, but
not set as a value on the message.

```php
$mail = new Message();

$from = $mail->getFrom('default@example.com');
// gives: array('default@example.com' => null)

$from = $mail->getFrom(array('default@example.com', 'trololo@example.com' => 'Mr. Trololo'));
// gives: array('default@example.com' => null, 'trololo@example.com' => 'Mr. Trololo')
```

The `Sender` and `ReturnPath` fields only support single email addresses and
will always return a maximum of one email address. All other fields like `To`,
`Cc`, `Bcc`, `ReplyTo` and even `From` support multiple email addresses.

# Configuration

The `Mail\Service` uses sensible default settings (like `utf-8` as the default
charset for everything). The settings are grouped in named mailers. Each
mailer is a set of settings that is known under a ```name``` attribute on the
```mailer``` element in the mail configuration. There is a ```default```
attribute on the ```mailers``` element to specify the default set of settings to
use.

There are multiple locations to change the mailer settings: Each Agavi module
has its own ```<module_name>/config/mail.xml``` file that may define default
settings for that module. The `mail.xml` file from a module usually specifies a
```parent="%core.config_dir%/mail.xml"``` attribute that leads to the inclusion
of the default ```app/config/mail.xml``` that can contain specific mailer
settings for Honeybee. That file usually XIncludes the concrete project and
application specific `app/project/config/mail.xml` file. You should either use
the module's `mail.xml` or create and customize project wide mailer settings in
that `app/project/config/mail.xml` file.

The configuration files are merged and then the containing `configuration`
elements are handled according to the natural order after merging while
maintaining the following priorities according to the presence of the
`environment` and/or `context` attributes: Configuration blocks that contain
both an `environment` and `context` attribute are preferred over `context` only
blocks which have higher precedence than `environment` only blocks that have
higher priority than normal vanilla blocks (without `context` or `environment`).
This means, that more specific blocks according to their attributes are winning
when settings of all blocks are merged into a representation for the mail
service.

## Settings

There are some default settings that are supported by the default mail service:

- ```override_all_recipients```: email address to use for `To`, `Cc` and `Bcc` regardless of other settings when those fields are set in a message
- ```default_subject```: string to use as the default subject if none is set for the message
- ```default_body_text```: string to use as the default plain text part of the mail body if none is set in a message
- ```default_body_html```: string to use as the default html part of the mail body if none is set in a message
- ```default_date```: default (unix) timestamp to set in a message if none is set - you may use a ```strtotime()``` compatible string like ```+2 weeks```
- ```address_defaults```: contains settings with "email field" => "email address(es)" pairs to use as defaults if a message does not set them
  - you can either specify an email address as string or a nested settings block with multiple addresses as settings
  - supported email field identifiers are: `to`, `from`, `cc`, `bcc`, ```reply_to```, ```return_path```, `sender`
- ```address_overrides```: contains settings with "email field" => "email address(es)" pairs to use instead of addresses already set on the message
  - you can either specify an email address as string or a nested settings block with multiple addresses as settings
  - supported email field identifiers are: `to`, `from`, `cc`, `bcc`, ```reply_to```, ```return_path```, `sender`
- ```max_line_length```: maximum length of lines in the plain text email part, defaults to 78 characters historically
- ```priority```: priority from 1 (highest) to 5 (lowest) used for mails, defaults to 3 (normal). Sets `X-Priority` header on the email.
- ```read_receipt_to```: email address to use for read receipt functionality

A very extensive example could look like this:

```xml
<ae:configuration environment="development.*">
    <mailers default="default">
        <mailer name="default">
            <settings>
                <setting name="override_all_recipients">%core.project_prefix%+%core.environment%@example.com</setting>
                <setting name="override_all_recipients">
                    <settings>
                        <setting>OVERRIDE_ALL@example.com</setting>
                        <setting>OVERRIDE_ALWAYS@example.com</setting>
                    </settings>
                </setting>
                <setting name="default_date">+2 weeks</setting>
                <setting name="default_body_html"><![CDATA[<h1>Hello from mail.xml!</h1>]]></setting>
                <setting name="default_body_text">Hello from the default_body_text in mail.xml. :-)</setting>
                <setting name="address_defaults">
                    <settings>
                        <setting name="bcc">default-bcc-%core.project_prefix%+%core.environment%@example.com</setting>
                        <setting name="sender">default-sender@example.com</setting>
                        <setting name="reply_to">default-reply-to@example.com</setting>
                    </settings>
                </setting>
                <setting name="address_overrides">
                    <settings>
                        <setting name="from">override-from@example.com</setting>
                        <setting name="to">
                            <settings>
                                <setting>override-to-someone@example.com</setting>
                                <setting>override-to-trololo@example.com</setting>
                            </settings>
                        </setting>
                        <setting name="cc">override-cc@example.com</setting>
                        <setting name="bcc">override-bcc@example.com</setting>
                        <setting name="return_path">override-return-path@example.com</setting>
                        <setting name="reply_to">override-reply-to@example.com</setting>
                    </settings>
                </setting>
            </settings>
        </mailer>
    </mailers>
</ae:configuration>
```

Address default will be used to set fields if they are empty upon sending a
message. Address overrides are used to override the field values or their
default values. If you do not want to redirect emails in any case to an address
of your choice, just set the ```override_all_recipients``` setting with an email
address. It will then be used to override `To`, `Cc` and `Bcc` if present.

# Using custom mailer settings

To use a different set of mailer settings (instead of the default ones) you can
specify a mailer name when sending a message. Just give an existing mailer name
as the second parameter to the `send()` method:

```php
$mail_service->send($mail, 'system_mails');
```

The ```system_mails``` mailer name should have been defined in one or more of
the `mail.xml` files similar to something like this:

```xml
<ae:configuration>
    <mailers name="default">
        <mailer name="system_mails">
            <settings>
                <setting name="charset">utf-8</setting>
                <setting name="default_subject">[ALERT] System Notification</setting>
                <setting name="address_defaults">
                    <settings>
                        <setting name="from">%core.project_prefix%+%core.environment%@example.com</setting>
                        <setting name="sender">system@example.com</setting>
                        <setting name="reply_to">admin@example.com</setting>
                        <setting name="return_path">bounces@example.com</setting>
                    </settings>
                </setting>
            </settings>
        </mailer>
    </mailers>
</ae:configuration>
```

If you now want to send system notifications, you can save on some typing as the
bounce address et cetera are already set correctly for that environment, module
or context (depending on the configuration block attributes and merging):

```php
$mail = new Message();
$mail->setBodyText('You should log in more often.');
$this->getModule()->getService('mail')->send($mail, 'system_mails');
// sent mail contains reply_to, return_path etc. from settings
```

# Transport modification

By default the ```Swift_SendmailTransport``` is used to send mail to the local
`sendmail` instance. If `sendmail` is not available you may specify another
transport class via the ```swift_transport_class``` setting.

```xml
<setting name="swift_transport_class">\Swift_NullTransport</setting>
```

As other transports may need to be configured, you can create your own mail
service class, that extends `Honeybee\Core\Mail\Service` and overwrites the
```initSwiftMailer()``` method appropriately. To e.g. use a SMTP transport one
could do this:

```php
class YourMailService extends \Honeybee\Core\Mail\Service
{
    /**
     * Initializes a \Swift_Mailer instance with a SMTP transport.
     *
     * @param string $mailer_name name of mailer to get settings for (if omitted, the settings of the default mailer are used)
     */
    public function initSwiftMailer($mailer_config_name = null)
    {
        $settings = $this->getMailerSettings($mailer_config_name);

        $host = $settings->get('smtp_host', 'localhost');
        $port = $settings->get('smtp_port', 25);
        $security = $settings->get('smtp_security'); // e.g. 'tls'

        $this->connection = \Swift_SmtpTransport::newInstance($host, $port, $security);
        $this->mailer = \Swift_Mailer::newInstance($this->connection);

        $charset = $settings->get('charset', 'utf-8');
        \Swift_Preferences::getInstance()->setCharset($charset);
    }
}
```

and then switch the Agavi setting ```<module_prefix>.service.mail```in the
`settings.xml` file. You could do it like this prior getting the service:

```php
AgaviConfig::set($module->getOption('prefix') . '.service.mail', 'YourMailService');
$module->getService('mail')->send($mail);
```

## Using Swiftmailer plugins

As the mail service uses Swiftmailer by default you can make use of existing
plugins for that library. Just get the service and then register plugins via
the ```registerPlugin()``` method prior to calling ```send()``` on the service.

Here are some examples that may be useful:

```php
$mail_service = $module->getService('mail');

// re-connect after 100 emails
$mail_service->getMailer()->registerPlugin(new \Swift_Plugins_AntiFloodPlugin(100));

// pause for 30 seconds after 100 mails
$mail_service->getMailer()->registerPlugin(new Swift_Plugins_AntiFloodPlugin(100, 30));

// rate limit to 100 emails per-minute
$mail_service->getMailer()->registerPlugin(new \Swift_Plugins_ThrottlerPlugin(
    100, \Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE
));

// rate limit to 10MB per-minute
$mail_service->getMailer()->registerPlugin(new \Swift_Plugins_ThrottlerPlugin(
    1024 * 1024 * 10, \Swift_Plugins_ThrottlerPlugin::BYTES_PER_MINUTE
));
```

## Support for other mailing libraries

As mentioned in the last paragraph you can create your own mail service instance
and switch to using it via the AgaviConfig setting if needed. Additionally you
can of course always use whatever library you like.

## TBD / Ideas / Misc

- configure and use SwiftMailer plugins by default?
- more settings (like transport configuration w/o an own mail service)?
- perhaps use https://github.com/egulias/EmailValidator for email validation?
