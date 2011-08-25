<?php

if (class_exists('Generic_Sniffs_Files_LineLengthSniff', true) === false)
{
    throw new PHP_CodeSniffer_Exception('Class Generic_Sniffs_Files_LineLengthSniff not found');
}

class BerlinOnline_Sniffs_Files_LineLengthSniff extends Generic_Sniffs_Files_LineLengthSniff
{
    /**
     * The limit that the length of a line should not exceed.
     *
     * @var int
     */
    public $lineLimit = 80;

    /**
     * The limit that the length of a line must not exceed.
     *
     * Set to zero (0) to disable.
     *
     * @var int
     */
    public $absoluteLineLimit = 120;

}

?>
