<?php

namespace Honeybee\Core\Storage\Memory;

class ZipArchive extends \ZipArchive
{
    protected $archvie_path;

    public function open($filename, $flags = null)
    {
        $this->archive_path = $filename;

        return parent::open($filename, $flags);
    }

    public function getArchivePath()
    {
        return $this->archive_path;
    }
}
