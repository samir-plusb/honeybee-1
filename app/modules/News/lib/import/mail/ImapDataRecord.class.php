<?php

/**
 * The ImapDataRecord class is a concrete implementation of the NewsDataRecord base class.
 * It serves as a DTO for mail data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Import/Mail
 */
class ImapDataRecord extends NewsDataRecord
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds a file prefix, which is passed to php's tempname method
     * in order to build a tmp filepath for writing a mail to the disk
     * for further processing.
     */
    const TMP_FILE_PREFIX = 'midas.mail.';


    private $tempFileName;

    // ---------------------------------- </CONSTANTS> -------------------------------------------

    /**
     * free system resources
     */
    public function __destruct()
    {
        $this->removeTmpFile();
    }

    // ---------------------------------- <NewsDataRecord IMPL> ------------------------------

    /**
     * Parse the given xml data and return a normalized array.
     * The provided $data argument is served by the ImapDataSource::fetchData method
     * and is expected to contain the raw mail data (textual representation of head and body concated).
     *
     * @param       string $data
     *
     * @return      array
     *
     * @see         NewsDataRecord::parseData()
     * @see         ImapDataSource::fetchData()
     */
    protected function parseData($data)
    {
        $parser = new ProjectMailParser(
            $this->writeMailToTmpFile($data)
        );

        $html = $parser->getMessageBody(ProjectMailParser::BODY_HTML);
        $text = $parser->getMessageBody(ProjectMailParser::BODY_TEXT);
        $this->removeTmpFile();

        return array(
            self::PROP_IDENT     => uniqid(),
            self::PROP_MEDIA     => $this->createAssets($parser->getAttachments()),
            self::PROP_TITLE     => $parser->getSubject(),
            self::PROP_TIMESTAMP => new DateTime($parser->getDate()),
            self::PROP_SOURCE    => $parser->getFrom(),
            self::PROP_CONTENT   => $text . $html,
            self::PROP_GEO       => array()
        );
    }

    // ---------------------------------- </NewsDataRecord IMPL> -----------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * unlink temporary generated file
     */
    protected function removeTmpFile()
    {
        if (! empty($this->tempFileName))
        {
            unlink($this->tempFileName);
            $this->tempFileName = NULL;
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
        $filePath = $this->tempFileName = tempnam(sys_get_temp_dir(), self::TMP_FILE_PREFIX);

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
                'fullName' => $attachedFile['name'],
                'mimeType' => $attachedFile['type']
            );
            $assetInfo = $assetService->put($fileUri, $metaData);
            $assetIds[] = $assetInfo->getIdentifier();
        }
        return $assetIds;
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>