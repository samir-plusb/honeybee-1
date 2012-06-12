<?php

/**
 * The WkgDataSource class is a concrete implementation of the BaseDataSource base class.
 * It provides fetching xml based wkg location/adress data.
 *
 * @version         $Id: WkgDataSource.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import/Wkg
 */
class WkgDataSource extends BaseDataSource
{
    /**
     * Holds the name of the node considered as the root of our data.
     */
    const ROOT_NODE_NAME = 'Listing';

    /**
     * Holds a directory iterator that we use to traverse a configured directory looking for wgk import files.
     *
     * @var ProjectDirectoryRegexpIterator
     */
    protected $directoryIterator;

    /**
     * Holds a xml reader that we use to traverse a single xml document's Listing nodes.
     *
     * @var XMLReader
     */
    protected $xmlReader;

    /**
     * Holds our current Listing node's xml string.
     *
     * @var string
     */
    protected $currentListing;

    /**
     * Setup the datasource.
     */
    protected function init()
    {
        $this->directoryIterator = NULL;
        $this->xmlReader = NULL;
        $this->currentListing = NULL;
    }

    /**
     * Fetch the data for the current iterator position.
     *
     * @return DOMElement
     */
    protected function fetchData()
    {
        return $this->currentListing;
    }

    /**
     * Forward our cursor to the next listing node.
     *
     * @return boolean True if there is a current listing avaiable, false if we have reached the end.
     */
    protected function forwardCursor()
    {
        // optimistic approach, lets try to get the next listing node.
        $this->currentListing = $this->forwardXmlReader();
        if ($this->currentListing)
        {
            return TRUE;
        }
        // hmm, didn't work, so either we finshed processing a file or are being called the first time.
        // in both cases we need to forward our directory iterator to the next file.
        if(! $this->forwardDirectoryIterator())
        {
            // hmm, directory iterator not valid? yay, I can haz finished!
            $this->currentListing = NULL;
            return FALSE;
        }
        // let's try again, if the xml aint broken things should work out now.
        // we could do this by recursivly calling forwardCursor, but lets keep it straight.
        $this->currentListing = $this->forwardXmlReader();

        return (NULL !== $this->currentListing);
    }

    /**
     * Forward our "outter" directory iterator to the next file.
     *
     * @return boolean True if we have a current file or false if we have reached the end.
     */
    protected function forwardDirectoryIterator()
    {
        if ($this->directoryIterator)
        {
            // we allready have an initialized iterator, just forward to the next file.
            $this->directoryIterator->next();
        }
        else
        {
            // intialize a new directory iterator instance.
            $this->directoryIterator = $this->createDirectoryIterator();
        }
        if ($this->directoryIterator->valid())
        {
            if ($this->xmlReader)
            {
                // close any existing xmlreader instance before creating the next one.
                $this->xmlReader->close();
            }
            // if we have a current file, init a new xml reader for the file.
            $this->xmlReader = $this->createXmlReader($this->directoryIterator->current());
            if (! $this->xmlReader)
            {
                // creating xml reader failed.
                return FALSE;
            }
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Forward our "inner" xml iterator to the next Listing node.
     *
     * @return string The next Listing node's xml-string from the current xml file or NULL if there is none.
     */
    protected function forwardXmlReader()
    {
        if (! $this->xmlReader)
        {
            return NULL;
        }
        // set our xmlreader to point to the first Listings node in the document.
        while ($this->xmlReader->read())
        {
            if (self::ROOT_NODE_NAME === $this->xmlReader->name && XMLReader::ELEMENT === $this->xmlReader->nodeType)
            {
                break;
            }
        }
        if (self::ROOT_NODE_NAME === $this->xmlReader->name)
        {
            return $this->xmlReader->readOuterXml();
        }
        // No listings node found in the given file.
        // So no xmlreader for you.
        return NULL;
    }

    /**
     * Factory method for creating ProjectDirectoryRegexpIterator instances,
     * initialized from our settings.
     *
     * @return ProjectDirectoryRegexpIterator
     */
    protected function createDirectoryIterator()
    {
        $directory = realpath($this->config->getSetting(WkgDataSourceConfig::CFG_DIRECTORY));
        $filePattern = $this->config->getSetting(WkgDataSourceConfig::CFG_FILE_PATTERN);
        return new ProjectDirectoryRegexpIterator($directory, $filePattern);
    }

    /**
     * Factory method for creating XMLReader instances for a given xml file.
     *
     * @param string $filePath
     *
     * @return XMLReader|null
     */
    protected function createXmlReader($filePath)
    {
        $xmlReader = new XMLReader();
        // lets try to load the given xml file.
        if (! $xmlReader->open($filePath))
        {
            return NULL;
        }
        return $xmlReader;
    }
}

?>
