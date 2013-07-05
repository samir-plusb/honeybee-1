<?php

namespace Honeybee\Core\Mail;

use Honeybee\Core\Mail\IMail;

/**
 * Interface that represents a simplified mailer.
 */
interface IMailer
{
    /**
     * Sends the given message instance via the configured mailer and transport.
     *
     * @param IMail $mail message to send
     *
     * @return mixed
     */
    public function send(IMail $message);

    /**
     * Returns the internally used mailer instance to allow for more advanced
     * use cases where the simple IMail interface is not sufficient at all.
     *
     * @return mixed concrete mailer instance used
     */
    public function getMailer();
}
