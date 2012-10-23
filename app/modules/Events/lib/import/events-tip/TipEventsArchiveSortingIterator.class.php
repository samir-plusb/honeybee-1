<?php 

class TipEventsArchiveSortingIterator extends ArrayIterator
{
    const FILE_READY_SUFFIX = '_ready';

    public function __construct($directoryPath, $processedMarker, $readyMarker = self::FILE_READY_SUFFIX)
    {
        $directoryIterator = new TipEventsArchiveIterator($directoryPath, $processedMarker, $readyMarker);

		$files = array();
        foreach ($directoryIterator as $file) 
        {
            $files[] = $file;
        }
    
        sort($files);

        parent::__construct($files);
    }
}
 
