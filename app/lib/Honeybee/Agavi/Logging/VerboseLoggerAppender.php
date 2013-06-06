<?php

namespace Honeybee\Agavi\Logging;

use Honeybee\Agavi\Logging\FileLoggerAppender;
use Honeybee\Agavi\Logging\LoggerManager;

/**
 * Extends the FileLoggerAppender message with various system, Agavi and
 * application information that may be helpful with debugging.
 */
class VerboseLoggerAppender extends FileLoggerAppender
{
    /**
     * Adds various system, Agavi and application specific debugging
     * information to the given logger message as a json string.
     *
     * @param \AgaviLoggerMessage $message
     *
     * @return void
     */
    public function write(\AgaviLoggerMessage $message)
    {
        $message_text = $message->getMessage();

        $extra = LoggerManager::getExtraInformation();

        $message->setMessage($message_text . ' extra=' . json_encode($extra));

        parent::write($message);
    }
}
