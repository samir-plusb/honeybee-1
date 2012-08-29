<?php

/**
 * The TipEventsArchiveIterator lets you traverse zip files coming from the tip eventx ftp export,
 * thereby only providing zip files that have a corresponding *_ready in the same directory.
 * This is a workaround to fix ftp-upload <-> import race conditions.
 *
 * @version         $Id: TipEventsArchiveIterator.class.php 1299 2012-06-12 16:09:14Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Events
 * @subpackage      Import/EventsTip
 */
class TipEventsArchiveIterator extends ProjectDirectoryRegexpIterator
{
    const FILTER_REGEXP = '(.*)\.zip';

    const FILE_READY_SUFFIX = '_ready';

    protected $readyMarker;

    protected $processedMarker;

    public function __construct($directoryPath, $processedMarker, $readyMarker = self:: FILE_READY_SUFFIX)
    {
        $this->readyMarker = $readyMarker;
        $this->processedMarker = $processedMarker;

        parent::__construct($directoryPath, self::FILTER_REGEXP);
    }

    public function accept()
    {
        if (! parent::accept())
        {
            return FALSE;
        }
        
        $filepath = $this->getInnerIterator()->current();
        $readyMarker = str_replace('.zip', $this->readyMarker, $filepath);
        $importedMarker = str_replace('.zip', $this->processedMarker, $filepath);

        return is_readable($readyMarker) && ! file_exists($importedMarker);
    }
}
