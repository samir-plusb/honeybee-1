<?php

class ProjectFileSystemRegexpIterator extends FilterIterator
{
    const REGEXP_DELIMITER = '~';

    protected $filterRegexp;

    public function __construct($directoryPath, $filterRegexp)
    {
        parent::__construct(
            new FilesystemIterator(
                $directoryPath,
                FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS
            )
        );

        $this->filterRegexp = sprintf(
            '%s%s%sis',
            self::REGEXP_DELIMITER,
            $filterRegexp,
            self::REGEXP_DELIMITER
        );

        // Make sure that we are valid for fresh instances.
        // Otherwise while($it->valid()) { $it->next(); } will skip the first item.
        $this->rewind();
    }

    public function accept()
    {
        $filePath = $this->getInnerIterator()->current();
        return (0 < preg_match($this->filterRegexp, $filePath));
    }

    public function getMTime()
    {
        return $this->getInnerIterator()->getMTime();
    }
}

?>
