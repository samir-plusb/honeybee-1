<?php

/**
 * The ImapDataSourceConfig class is a concrete implementation of the DataSourceConfig base class.
 * It serves as the main config object for the ImapDataSource class.
 * 
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Mail
 */
class ImapDataSourceConfig extends DataSourceConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds the name of our imap 'host' setting 
     * that exposes the host we use to connect our mailbox handle.
     */
    const CFG_HOST = 'host';
    
    /**
     * Holds the name of our imap 'port' setting 
     * that exposes the port we use to connect our mailbox handle.
     */
    const CFG_PORT = 'port';
    
    /**
     * Holds the name of our imap 'protocol' setting 
     * that exposes the protocol we use to connect our mailbox handle.
     */
    const CFG_PROTOCOL = 'protocol';
    
    /**
     * Holds the name of our imap 'mailbox' setting 
     * that exposes the mailbox we use to connect our mailbox handle.
     */
    const CFG_MAILBOX = 'mailbox';
    
    /**
     * Holds the name of our imap 'username' setting that exposes our imap username.
     */
    const CFG_USERNAME = 'username';
    
    /**
     * Holds the name of our imap 'password' setting that exposes our imap password.
     */
    const CFG_PASSWORD = 'password';
    
    // ---------------------------------- </CONSTANTS> -------------------------------------------
    
    
    // ---------------------------------- <DataSourceConfig OVERRIDES> ---------------------------
    
    /**
     * Return an array of settings names,
     * that must be provided by our config source.
     * 
     * @return      array
     * 
     * @see         DataSourceConfig::getRequiredSettings()
     */
    public function getRequiredSettings()
    {
        return array_merge(
            parent::getRequiredSettings(),
            array(
                self::CFG_USERNAME,
                self::CFG_PASSWORD,
                self::CFG_PORT,
                self::CFG_HOST,
                self::CFG_MAILBOX,
                self::CFG_PROTOCOL
            )
        );
    }
    
    // ---------------------------------- <DataSourceConfig OVERRIDES> ---------------------------
}

?>