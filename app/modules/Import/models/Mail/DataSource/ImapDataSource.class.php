<?php

/**
 * The ImapDataSource class is a concrete implementation of the ImportBaseDataSource base class.
 * It provides access to email based data by using an configured imap/pop3 account as it' source.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Mail
 */
class ImapDataSource extends ImportBaseDataSource
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of the array key where we expect our header,
     * which passed to to our parseData() method.
     */
    const DATA_FIELD_HEADER = 'header';

    /**
     * Holds the name of the array key where we expect our rawData,
     * which passed to to our parseData() method.
     */
    const DATA_FIELD_RAW_DATA = 'rawData';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds our mailbox handle.
     *
     * @var         resource
     */
    protected $mailBoxConnection;

    /**
     * Holds our current position while iterating over our mails.
     *
     * @var         int
     */
    protected $cursorPos;

    /**
     * Holds the max number of iterations possible,
     * depending on the number of available mails.
     *
     * @var         int
     */
    protected $maxCount;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <ImportBaseDataSource OVERRIDES> -----------------------

    /**
     * Initialize our datasource, hence connect our mailbox
     * and init our iteration variables.
     */
    protected function init()
    {
        if ($this->config->getSetting(ImapDataSourceConfig::PARAM_MAILITEM, FALSE))
        {
            $this->maxCount = 1;
        }
        else
        {
            $this->connectMailbox();

            if (($header = imap_check($this->mailBoxConnection)))
            {
                $this->maxCount = $header->Nmsgs;
            }
            else
            {
                throw new DataSourceException(
                    "Failed to retrieve mailbox header information."
                );
            }
        }

        $this->cursorPos = 0;
    }

    /**
     * Forward our current position inside the mail list
     * that we are currently iterating.
     *
     * @return      boolean
     */
    protected function forwardCursor()
    {
        $this->cursorPos++;

        return $this->cursorPos <= $this->maxCount;
    }

    /**
     * Return the data of the mail our cursor is currently pointing to.
     *
     * @return      array
     */
    protected function fetchData()
    {
        $rawMimeMail = $this->config->getSetting(ImapDataSourceConfig::PARAM_MAILITEM, FALSE);

        if (! $rawMimeMail)
        {
            $textHeader = imap_fetchheader($this->mailBoxConnection, $this->cursorPos, FT_PREFETCHTEXT);
            $header = imap_headerinfo($this->mailBoxConnection, $this->cursorPos);
            $rawBody = imap_body($this->mailBoxConnection, $this->cursorPos, FT_PEEK);
            $rawMimeMail = $textHeader . $rawBody;
        }

        return $rawMimeMail;
    }

    // ---------------------------------- </ImportBaseDataSource OVERRIDES> ----------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Connect to our configured imap/pop3 account.
     */
    protected function connectMailbox()
    {
        $server = sprintf(
            "{%s/%s:%s}%s",
            $this->config->getSetting(
            ImapDataSourceConfig::CFG_HOST),
            $this->config->getSetting(ImapDataSourceConfig::CFG_PROTOCOL),
            $this->config->getSetting(ImapDataSourceConfig::CFG_PORT),
            $this->config->getSetting(ImapDataSourceConfig::CFG_MAILBOX)
        );

        $user = $this->config->getSetting(ImapDataSourceConfig::CFG_USERNAME);
        $pass = $this->config->getSetting(ImapDataSourceConfig::CFG_PASSWORD);

        $mailBox = NULL;

        if (!($mailBox = imap_open($server, $user, $pass)))
        {
            throw new DataSourceException("Could not open Mailbox - try again later!");
        }

        $this->mailBoxConnection = $mailBox;
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------

}

?>