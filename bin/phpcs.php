<?php
/**
 * PHP_CodeSniffer tokenises PHP code and detects violations of a
 * defined set of coding standards.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: phpcs 301176 2010-07-12 04:36:44Z squiz $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

ini_set('include_path', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'libs' . PATH_SEPARATOR . get_include_path());

error_reporting(E_ALL | E_STRICT);

// Optionally use PHP_Timer to print time/memory stats for the run.
// Note that the reports are the ones who actually print the data
// as they decide if it is ok to print this data to screen.
@include_once 'PHP/Timer.php';
if (class_exists('PHP_Timer', false) === true) {
    PHP_Timer::start();
}

if (is_file(dirname(__FILE__).'/../CodeSniffer/CLI.php') === true) {
    include_once dirname(__FILE__).'/../CodeSniffer/CLI.php';
} else {
    include_once 'PHP/CodeSniffer/CLI.php';
}

$phpcs = new PHP_CodeSniffer_CLI();
$phpcs->checkRequirements();

$numErrors = $phpcs->process();
if ($numErrors === 0) {
    exit(0);
} else {
    exit(1);
}

?>
