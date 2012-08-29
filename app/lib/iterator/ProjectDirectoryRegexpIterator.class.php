<?php

/**
 * The ProjectFileSystemRegexpIterator lets you traverse files on the file system thereby applying
 * a given regexp to iterate only files that the expression.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Project
 * @subpackage      Iterator
 */
class ProjectDirectoryRegexpIterator extends FilterIterator
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
            '%1$s%2$s%1$sis',
            self::REGEXP_DELIMITER,
            $filterRegexp
        );
    }

    public function accept()
    {
        $fileName = basename($this->getInnerIterator()->current());
        return (0 < preg_match($this->filterRegexp, $fileName));
    }

    public function getMTime()
    {
        return $this->getInnerIterator()->getMTime();
    }
}

?>
