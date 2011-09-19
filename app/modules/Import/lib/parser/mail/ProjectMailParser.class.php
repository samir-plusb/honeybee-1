<?php

/**
 * The ProjectMailParser extends the MimeMailParser vendor library class
 * and adds in some usefull stuff, such as encoding awareness, a bit more convenient access
 * to dates, subject and mail body and documents the available headers/bodies via constants.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Parser
 */
class ProjectMailParser extends MimeMailParser
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of the mail header's 'content-type' field.
     */
    const HEADER_CONTENT_TYPE = 'content-type';

    /**
     * Holds the name of the mail header's 'subject' field.
     */
    const HEADER_SUBJECT = 'subject';

    /**
     * Holds the name of the mail header's 'date' field.
     */
    const HEADER_DATE = 'date';

    /**
     * Holds the name of the mail header's 'from' field.
     */
    const HEADER_FROM = 'from';

    /**
     * Holds the name of the mail body's 'text' content field.
     */
    const BODY_TEXT = 'text';

    /**
     * Holds the name of the mail body's 'html' content field.
     */
    const BODY_HTML = 'html';

    /**
     * Holds a string that represents the UTF-8 encoding 
     * and can be used with mb_* string functions.
     */
    const ENC_UTF8 = 'UTF-8';

    /**
     * Holds a flag that represents a 'no value' state
     * and is passed to iconv methods.
     */
    const ICONV_MIME_DECODE_NULL_FLAG = 0;

    /**
     * Holds a file prefix, which is passed to php's tempname method
     * in order to build a tmp filepath for writing a mail attachments to the disk
     * for further processing.
     */
    const ATTACHMENT_FILE_PREFIX = 'midas.attached.';

    // ---------------------------------- </CONSTANTS> -------------------------------------------
     
    
    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * An array of strings that we  search and remove from our subject.
     * 
     * @var         array
     */
    protected static $subjectReplacements = array('WG:', 'FW:', 'AW:', 'RE:');

    /**
     * Holds an encoding identifier that represents
     * the encoding to used for exposed textual content.
     * This var is used with iconv* to convert strings
     * that have an other than the desired output encoding.
     * 
     * @var         string
     */
    private $textEncoding;

    /**
     * Holds the different mail bodies, that we have allready parsed.
     * 
     * @var         array
     */
    private $body = array();

    /**
     * Holds our mail's subject.
     * 
     * @var         string
     */
    private $subject;

    /**
     * Holds our mail's sender.
     * 
     * @var         string
     */
    private $from;

    /**
     * An array of dates that we allready have parsed.
     * 
     * @var         array
     */
    private $dates = array();

    // ---------------------------------- </MEMBERS> ---------------------------------------------
     
    
    // ---------------------------------- <CONSTRUCTIR> ------------------------------------------

    /**
     * Create a new ProjectMailParser instance thereby 
     * handling an optionally passed $filePath pointing
     * to a file that contains raw mime-mail content.
     * 
     * @param       string $filePath 
     */
    public function __construct($filePath = NULL, $textEncoding = self::ENC_UTF8)
    {
        parent::__construct();

        $this->textEncoding = $textEncoding;

        if ($filePath)
        {
            $this->setPath($filePath);
        }
    }

    // ---------------------------------- </CONSTRUCTIR> -----------------------------------------
     
     
    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    /**
     * Returns the encoding used for all exposed textual content.
     * 
     * @return      string
     */
    public function getOutputEncoding()
    {
        return $this->textEncoding;
    }

    /**
     * Fetch a mail body(text|html) from our mail data.
     * 
     * @param       string $type
     * 
     * @return      string 
     */
    public function getMessageBody($type = self::BODY_TEXT)
    {
        if (isset($this->body[$type]))
        {
            return $this->body[$type];
        }

        $body = parent::getMessageBody($type);
        $currentEncoding = $this->getCharset();
        $targetEncoding = $this->getOutputEncoding();

        if ($targetEncoding !== $currentEncoding)
        {
            $body = mb_convert_encoding($body, $targetEncoding, $currentEncoding);
        }

        $this->body[$type] = $body;

        return $body;
    }

    /**
     * Return the mails subject.
     * 
     * @return      string
     */
    public function getSubject()
    {
        if (!$this->subject)
        {
            $subject = iconv_mime_decode(
                $this->getHeader(self::HEADER_SUBJECT), 
                self::ICONV_MIME_DECODE_NULL_FLAG, 
                $this->getOutputEncoding()
            );

            $this->subject = str_ireplace(
                self::$subjectReplacements, '', trim($subject)
            );
        }

        return $this->subject;
    }

    /**
     * Return a the given date $type in the iso8601 format.
     * 
     * @return      string A date string in the ISO8601 format.
     */
    public function getDate($type = self::HEADER_DATE)
    {
        if (!isset($this->dates[$type]))
        {
            $this->dates[$type] = date(
                DATE_ISO8601, strtotime(
                    $this->getHeader($type)
                )
            );
        }

        return $this->dates[$type];
    }

    /**
     * Returns our mail's sender.
     * 
     * @return      string 
     */
    public function getFrom()
    {
        if (!$this->from)
        {
            $this->from = $this->getAddress(self::HEADER_FROM);
        }

        return $this->from;
    }
    
    /**
     * Returns an with attachments that have been written
     * to tempfiles on the disk for further processing.
     * 
     * @return      array 
     */
    public function getAttachments()
    {
        $attachments = parent::getAttachments();
        $inlineAttachments = parent::getInlineAttachments();
        $attachments = array_merge($attachments, $inlineAttachments);
        $attachmentFiles = array();

        foreach ($attachments as $attachment)
        {
            $attachmentFiles[] = $this->writeAttachmentToTmpFile($attachment);
        }

        return $attachmentFiles;
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------
     
     
    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Writes a given mail attachment to a temp file
     * and returns the path.
     * 
     * @param       MimeMailParser_attachment $attachment
     * 
     * @return      string 
     */
    protected function writeAttachmentToTmpFile(MimeMailParser_attachment $attachment)
    {
        $filePath = tempnam(sys_get_temp_dir(), self::ATTACHMENT_FILE_PREFIX);

        if (($filePtr = fopen($filePath, 'w')))
        {
            while ($bytes = $attachment->read())
            {
                fwrite($filePtr, $bytes);
            }

            fclose($filePtr);
        }
        else
        {
            throw new Exception(
                "Failed to create tmp file processing mail attachment."
            );
        }

        return array(
            'type' => $attachment->getContentType(),
            'name' => $attachment->getFilename(),
            'origin' => $this->getFrom(),
            'path' => $filePath
        );
    }

    /**
     * Parse and return a given adress the type from the mail data.
     * 
     * @param       string $type
     * 
     * @return      string
     * 
     * @todo        This method needs review badly. ^^
     */
    protected function getAddress($type = self::HEADER_FROM)
    {
        $address = $this->getHeader($type);
        $body = $this->getMessageBody(self::BODY_TEXT) . $this->getMessageBody(self::BODY_HTML);

        if ($address && $body)
        {
            $check = preg_match('/\b([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})\b/sim', $address, $mail);
            $verlag = '/berliner-verlag|berliner-zeitung|berliner-kurier|berlinonline|tip-berlin/i';

            if (preg_match($verlag, $address) || !$check)
            {
                preg_match('/(Von|From):.*?(\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b)/sim', $body, $match);

                if (!empty($match[2]))
                {
                    return $match[2];
                }
            }
            else
            {
                return $mail[1];
            }
        }

        return FALSE;
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>