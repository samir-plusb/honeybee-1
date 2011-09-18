<?php

/**
 * The ImapDataRecord class is a concrete implementation of the ImportBaseDataRecord base class.
 * It serves as a DTO for mail data.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Mail
 */
class ImapDataRecord extends ImportBaseDataRecord
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds the name of our timestamp property.
     */
    const PROP_TIMESTAMP = 'timestamp';
    
    /**
     * Holds a file prefix, which is passed to php's tempname method
     * in order to build a tmp filepath for writing a mail to the disk
     * for further processing.
     */
    const TMP_FILE_PREFIX = 'midas.mail.';
    
    // ---------------------------------- </CONSTANTS> -------------------------------------------
    
    
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds our pub-timestamp in the ISO8601 format.
     * 
     * @var         string
     */
    protected $timestamp;
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------
    
    /**
     * Return our timestamp date string.
     * 
     * @return      string A date string in the ISO8601 format. 
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
    
    // ---------------------------------- </PUBLIC METHODS> --------------------------------------
    
    
    // ---------------------------------- <ImportBaseDataRecord::Hydrate SETTERS> ----------------
    
    /**
     * Sets our timestamp/date.
     * 
     * @param       string $timestamp 
     */
    protected function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }
    
    // ---------------------------------- </ImportBaseDataRecord::Hydrate SETTERS> ---------------
    
    
    // ---------------------------------- <XmlBasedDataRecord OVERRIDES> -------------------------
    
    /**
     * Return an array holding property names of properties,
     * which we want to expose through our IDataRecord::toArray() method.
     *
     * @return      array
     * 
     * @see         XmlBasedDataRecord::getExposedProperties()
     */
    protected function getExposedProperties()
    {
        return array_merge(
            parent::getExposedProperties(),
            array(self::PROP_TIMESTAMP)
        );
    }
    
    // ---------------------------------- </XmlBasedDataRecord OVERRIDES> ------------------------
    
    
    // ---------------------------------- <XmlBasedDataRecord IMPL> ------------------------------
    
    /**
     * Parse the given xml data and return a normalized array.
     * The provided $data argument is served by the ImapDataSource::fetchData method
     * and is expected to contain the raw mail data (textual representation of head and body concated)
     * and a header stdclass object.
     * The data is expected to be passed in the following structure:
     * array(
     *     'header'  => @see imap_headerinfo(),
     *     'rawData' => imap_fetchheader(FT_PREFETCHTEXT) . imap_body(FT_PEEK)
     * )
     * 
     * @param       mixed $data
     *
     * @return      array
     *
     * @see         ImportBaseDataRecord::parseData()
     * @see         ImapDataSource::fetchData()
     */
    protected function parseData($data)
    {
        $this->checkData($data);
        
        $parser = new ProjectMailParser(
            $this->writeMailToTmpFile($data[ImapDataSource::DATA_FIELD_RAW_DATA])
        );
        
        $html = $parser->getMessageBody(ProjectMailParser::BODY_HTML);
        $text = $parser->getMessageBody(ProjectMailParser::BODY_TEXT);
        
        return array(
            self::PROP_IDENT     => $data[ImapDataSource::DATA_FIELD_HEADER]->message_id,
            self::PROP_MEDIA     => $this->createAssets($parser->getAttachments()),
            self::PROP_TITLE     => $parser->getSubject(),
            self::PROP_TIMESTAMP => $parser->getDate(),
            self::PROP_SOURCE    => $parser->getFrom(),
            self::PROP_CONTENT   => $text . $html,
            self::PROP_GEO       => array()
        );
    }
    
    // ---------------------------------- </XmlBasedDataRecord IMPL> -----------------------------
    
    
    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    /**
     * Checks the data we have received from a datasource for consistence.
     * 
     * @param       mixed $data Expected to be an array with the key 'header' and 'rawData'.
     * 
     * @see         ImapDataRecord::DATA_FIELD_HEADER
     * @see         ImapDataRecord::DATA_FIELD_RAW_DATA
     */
    protected function checkData($data)
    {
        if (
            ! is_array($data) || 
            ! isset($data[ImapDataSource::DATA_FIELD_HEADER]) || 
            ! isset($data[ImapDataSource::DATA_FIELD_RAW_DATA])
        )
        {
            throw new DataRecordException(
                "Invalid data passed to ImapDataRecord instance's parseData method."
            );
        }
    }
    
    /**
     * Write the given mime-mail data to a tmp file.
     * 
     * @param       string $rawMail The raw mime content.
     * 
     * @return      string The filepath the file has been written to.
     */
    protected function writeMailToTmpFile($rawMail)
    {
        $filePath = tempnam(sys_get_temp_dir(), self::TMP_FILE_PREFIX);
        
        if (! file_put_contents($filePath, $rawMail))
        {
            throw new DataRecordException(
                "Failed to create tmp-file for mail parsing for tmp-path: " . $filePath
            );
        }
        
        return $filePath;
    }
    
    /**
     * Takes an array with filepaths pointing to files attached to the current mail
     * and drops them into our asset service, thereby creating new persistent assetinfo instances.
     * 
     * @param       array $attachedFiles
     * 
     * @return      array
     */
    protected function createAssets(array $attachedFiles)
    {
        $assetIds = array();
        $assetService = ProjectAssetService::getInstance();
        
        foreach ($attachedFiles as $attachedFile)
        {
            $fileUri = 'file://' . $attachedFile['path'];
            $metaData = array(
                ProjectAssetInfo::XPROP_FULLNAME  => $attachedFile['name'],
                ProjectAssetInfo::XPROP_MIME_TYPE => $attachedFile['type']
            );
            
            $assetInfo = $assetService->put($fileUri, $metaData);
            $assetIds[] = $assetInfo->getId();
        }
        
        return $assetIds;
    }
    
    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>