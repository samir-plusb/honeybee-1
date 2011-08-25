<?php

if (class_exists('PHP_CodeSniffer_Standards_CodingStandard', true) === false)
{
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_CodingStandard not found');
}

/**
 * BerlinOnline Coding Standard.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */
class PHP_CodeSniffer_Standards_BerlinOnline_BerlinOnlineCodingStandard
{
    /**
     * Return a list of external sniffs to include with this standard.
     *
     * The BerlinOnline standard uses some PEAR sniffs.
     *
     * @return array
     */
    public function getIncludedSniffs()
    {
        return array(
            'Generic/Sniffs/Functions/OpeningFunctionBraceBsdAllmanSniff.php',
            'Generic/Sniffs/PHP/DisallowShortOpenTagSniff.php',
            'Generic/Sniffs/WhiteSpace/DisallowTabIndentSniff.php',
            'PEAR/Sniffs/Classes/ClassDeclarationSniff.php',
            'PEAR/Sniffs/ControlStructures/ControlSignatureSniff.php',
            'PEAR/Sniffs/Files/LineEndingsSniff.php',
            'PEAR/Sniffs/Functions/FunctionCallArgumentSpacingSniff.php',
            'PEAR/Sniffs/Functions/FunctionCallSignatureSniff.php',
            'PEAR/Sniffs/Functions/ValidDefaultValueSniff.php',
            'PEAR/Sniffs/WhiteSpace/ScopeClosingBraceSniff.php',
            'Squiz/Sniffs/Functions/GlobalFunctionSniff.php'
        );
    }
}

?>
