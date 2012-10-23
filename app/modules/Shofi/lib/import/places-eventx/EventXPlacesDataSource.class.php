<?php

/**
 * The EventXPlacesDataSource class is a concrete implementation of the BaseDataSource base class.
 * It provides fetching xml based places data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import/Hotel
 */
class EventXPlacesDataSource extends ArrayDataSource
{
    /**
     * Holds our current working directory.
     *
     * @var string $cwd
     */
    protected $cwd;

    /**
     * Holds an traversable list of zip archives (containing a set of files for one import ) to import.
     *
     * @var ProjectDirectoryRegexpIterator $archives
     */
    protected $archives;

    /**
     * Holds an array of file to import.
     *
     * @var array $files
     */
    protected $files;

    /**
     * Holds an array representation of the currently loaded file.
     *
     * @var array $data
     */
    protected $data;

    /**
     * Reflects the current cursor position of our data array.
     *
     * @var int $cursor
     */
    protected $cursor;

    /**
     * Holds the xml parser used to parse our source file.
     *
     * @var EventXPlacesXmlParser $parser
     */
    protected $parser;

    /**
     * Initializes the datasource, thereby creating our xml parser
     * and loading up the first file to process.
     */
    protected function init()
    {
        $this->cwd = $this->fetchWorkingDirectoryPath();
        $this->deleteDirectory($this->cwd, FALSE);

        $fileSettings = $this->config->getSetting(EventXPlacesDataSourceConfig::CFG_FILES);
        $this->files = array($fileSettings['setting']);

        $this->loadNextArchive();
    }

    /**
     * Return the name of the (eventx xml) file currently being processed,
     * in the process of defining our currently used data origin.
     *
     * @var string
     */
    protected function getCurrentOrigin()
    {
        return basename(current($this->files));
    }

    // ---------------------------------------------
    // ------------------------------ ITERATING DATA
    // ---------------------------------------------

    /**
     * Traverses our current data.
     * When the end of data is reached, the next file is loaded
     * untill we have also reached the end of our files list.
     *
     * @return bool Returns false when the cursor becomes invalid, else true.
     */
    protected function forwardCursor()
    {
        if (! $this->archives->valid())
        {
            return FALSE;
        }

        return (++$this->cursor < count($this->data)) || 
            $this->loadNextFile() || 
            $this->loadNextArchive();
    }

    /**
     * Returns the entry for the current cursor postion in our data array.
     *
     * @var array
     */
    protected function fetchData()
    {
        return isset($this->data[$this->cursor]) ? $this->data[$this->cursor] : FALSE;
    }

    // ----------------------------------------------
    // ------------------------------ ITERATING FILES
    // ----------------------------------------------

    /**
     * Loads the next file's parsed content into our data member
     * and resets the cursor.
     *
     * @return bool Returns false as soon as the end of our files list is reached, else true.
     */
    protected function loadNextFile()
    {
        // rewind selecta, means reset sursor and data
        $this->cursor = 0;
        $this->data = array();

        $next = each($this->files);
        if (FALSE === $next)
        {
            echo "End of file for the current archive " . $this->archives->current() . PHP_EOL;
            $this->markArchiveAsImported($this->archives->current());
            $this->deleteDirectory($this->cwd, FALSE);
            
            return FALSE;
        }

        $file = realpath($next['value']);
        if (! $file)
        {
            echo "YO DAWG, THIS FILE AINT AT A READBALE LOCATION!: " . $file . PHP_EOL; // @todo Do the log dog!
            
            return $this->loadNextFile(); // retry recursively until we run out of files ...
        }

        echo PHP_EOL . 'Starting to parse ' . $file . PHP_EOL;
        $this->data = $this->parser->parseXml($file);
        echo 'Counted ' . count($this->data) . ' items' . PHP_EOL;

        if (empty($this->data))
        {
            return $this->loadNextFile();
        }

        return TRUE;
    }

    // ----------------------------------------------------------
    // ------------------------------ ITERATING ZIP-ARCHIVE FILES
    // ----------------------------------------------------------

    /**
     * Load the next zip archive from our source directory.
     *
     * @return boolean
     */
    protected function loadNextArchive()
    {
        
        // Reset files-array to set internal cursor to the begin of the array
        reset($this->files);

        if (($nextArchive = $this->forwardArchiveIterator()))
        {
            if (! $this->extractArchive($nextArchive))
            {
                return $this->loadNextArchive();
            }
            
            $this->parser = $this->createParser();
            if (! $this->loadNextFile())
            {
                echo "Could not load, forgive the toad...." . PHP_EOL;
                return $this->loadNextArchive();
            }
            else
            {
                return TRUE;
            }
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Forward our zip file iterator to the next archive.
     *
     * @return string The absolut path of the current archive file.
     */
    protected function forwardArchiveIterator()
    {
        if (! $this->archives)
        {
            //$this->archives = new TipEventsArchiveIterator($this->cwd, '_adr_imported');
            $this->archives = new TipEventsArchiveSortingIterator($this->cwd, '_adr_imported');
        }
        else
        {
            $this->archives->next();
        }

        return $this->archives->current();
    }

    /**
     * Extract the given archive path to our import working directory.
     *
     * @return boolean
     */
    protected function extractArchive($archivePath)
    {
        $archive = new ZipArchive();
    
        echo PHP_EOL . PHP_EOL . 'Trying to extract archive ' . basename($archivePath) . PHP_EOL;
    
        if (TRUE === $archive->open($archivePath))
        {
            if (! $archive->extractTo($this->cwd))
            {
                echo "Failed extracting zip-archive to dest: " . PHP_EOL;
            }
            $archive->close();

            return TRUE;
        }
        else
        {
            echo "Unable to open zip, trying next archive: " . $archivePath . PHP_EOL;

            return FALSE;
        }
    }

    // ----------------------------------------------
    // ------------------------------ FACTORY METHODS
    // ----------------------------------------------

    /**
     * Creates and returns a new EventXPlacesXmlParser instance.
     *
     * @return EventXPlacesXmlParser
     */
    protected function createParser()
    {
        $eventsXmlSchemaPath = $this->config->getSetting(EventXPlacesDataSourceConfig::CFG_SCHEMA);
        $parser = new EventXPlacesXmlParser($eventsXmlSchemaPath);

        return $parser;
    }

    // -----------------------------------------------
    // ------------------------------ FILESYSTEM STUFF
    // -----------------------------------------------

    /**
     * Fetch our working dir name from the config and make sure it's writeable
     * and ends with a slash for easier concatination.
     *
     * @return string
     */ 
    protected function fetchWorkingDirectoryPath()
    {
        $cwd = realpath(
            $this->config->getSetting(EventXPlacesDataSourceConfig::CFG_SRC_DIRECTORY)
        );
        if (DIRECTORY_SEPARATOR !== $cwd{strlen($cwd) - 1})
        {
            $cwd .= DIRECTORY_SEPARATOR;
        }

        if (! is_writable($cwd))
        {
             throw new Exception(
                "The working directory for the event-tips import needs to be writeable (zip unpacking etc.)."
            );
        }
        
        return $cwd;
    }

    /**
     * Deletes (recursively) a given directory and it's contents.
     *
     * @param string $directory The directory to delete
     * @param boolean $deleteSelf
     */
    protected function deleteDirectory($directory, $deleteSelf = TRUE)
    {
        foreach(glob($directory . '/*') as $inode) 
        {
            if(is_dir($inode))
            {
                $this->deleteDirectory($inode);
            }
            else if (! preg_match('/(.*)(\.zip|_ready|_imported)$/is', $inode))
            {
                unlink($inode);
            }
        }

        if ($deleteSelf)
        {
            rmdir($directory);
        }
    }

    /**
     * Mark the given archive as imported by creating corresponding *_imported
     * file in our working directory.
     *
     * @param string $archivePath
     */
    protected function markArchiveAsImported($archivePath)
    {
        $importMarkerFile = str_replace(
            '.zip',  
            '_adr_imported', 
            $archivePath
        );
        // create import marker so we wont import this file again the next time.
        touch($importMarkerFile);
    }
}
