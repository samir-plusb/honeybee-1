<?php

use Honeybee\Domain\User\UserDocument;
use Honeybee\Core\Security\Auth;

/**
 * Simple validator that checks minimum complexity rules of a (password) string
 * by counting the number of occurrences of certain character classes and
 * optionally checking for similarity to other request parameters and even
 * optionally matching the string against entries from a blacklist text file.
 *
 * Parameters:
 *
 * - min_decimal_numbers: minimum number of decimal numbers required, defaults to 0
 * - min_uppercase_chars: minimum number of uppercase characters required, defaults to 0
 * - min_lowercase_numbers: minimum number of lowercase numbers required, defaults to 0
 * - min_string_length: minimum number of characters required, defaults to 6
 * - max_string_length: maximum number of characters required, defaults to 255
 *
 * - argument_names_for_similarity_check: single argument name or an array of
 *     argument names (like the username etc.) to enable a similarity comparison
 *     within the constraints given in the next two parameters.
 *     If this parameter is not defined, the similarity thresholds below are not
 *     taken into account as no comparisons are done.
 * - similarityPercentage_threshold: maximum allowed similarity of given
 *     similarity check arguments and the password, defaults to 80 percent
 *     (above that threshold the strings are deemed too similar)
 * - minimumLevenshteinDistance: minimum number of characters that must be
 *     different between similarity check arguments and the password
 *
 * - common_passwords_blacklist_file: optional parameter to specify a text file
 *     with common or blacklisted passwords (line by line). If the given
 *     password candidate matches (case-sensitive) one of the strings in the
 *     file, the validator fails. Please note, that this features is not speed
 *     optimized. Simple test takes about a second for about 50000 entries.
 *
 *
 * Usage example:
 *
 * <pre><code>
 * 
 * <validator class="PasswordComplexityValidator" name="minimum_password_complexity">
 *     <argument>login_pass</argument>
 *     <error>  Your password does not meet the minimum complexity rules.&lt;br/&gt;&lt;br/&gt;
 *It must contain at least 6 characters with at least 1 uppercase character, 1 lowercase character and 1 decimal number.
 *Please note, that it should also not be too similar to your login name, email or company name.</error>
 *     <ae:parameters>
 *         <ae:parameter name="min_decimal_numbers">1</ae:parameter>
 *         <ae:parameter name="min_uppercase_chars">1</ae:parameter>
 *         <ae:parameter name="min_lowercase_chars">1</ae:parameter>
 *         <ae:parameter name="min_string_length">6</ae:parameter>
 *         <ae:parameter name="max_string_length">32</ae:parameter>
 *
 *         <ae:parameter name="argument_names_for_similarity_check">
 *             <ae:parameter>login_name</ae:parameter>
 *             <ae:parameter>email</ae:parameter>
 *             <ae:parameter>company_name</ae:parameter>
 *         </ae:parameter>
 *         <ae:parameter name="similarityPercentage_threshold">80</ae:parameter>
 *         <ae:parameter name="minimumLevenshteinDistance">4</ae:parameter>
 * 
 *         <ae:parameter name="common_passwords_blacklist_file">%core.app_dir%/path/to/common_or_blacklisted_passwords.txt</ae:parameter>
 *     </ae:parameters>
 * </validator>
 *
 * </code></pre>
 *
 * @author Steffen Gransow <steffen.gransow@mivesto.de>
 */
class PasswordValidator extends UserTokenValidator
{
    const ERR_MISMATCH = 'repeat_password_mismatch';

    const ERR_SIMILARITY = 'password_too_similar';

    const ERR_UPPERCASE_TOKENS = 'not_enough_uppercase_tokens';

    const ERR_LOWERCASE_TOKENS = 'not_enough_lowercase_tokens';

    const ERR_NUMERIC_TOKENS = 'not_enough_numeric_tokens';

    const ERR_SPECIAL_CHARS = 'not_enough_special_chars';

    const ERR_TOO_SHORT = 'too_short';

    const ERR_TOO_LONG = 'too_long';

    const ERR_EASY_GUESSABLE = 'too_easy_to_guess';

    protected function validate()
    {
        $success = FALSE;
        $passwordCandidate = $this->getData($this->getArgument());

        if ($user = $this->loadUser())
        {
            $this->checkPasswordComplexity($user, $passwordCandidate);

            if (($repeatPasswordArgumentName = $this->getParameter('repeat_password_argument_name')))
            {
                $repeatPasswordArgument = $this->getData($repeatPasswordArgumentName);

                if ($repeatPasswordArgument !== $passwordCandidate)
                {
                    $this->throwError(self::ERR_MISMATCH);
                }
            }

            if (TRUE === $this->getParameter('clear_password', TRUE))
            {
                $this->export(NULL, $this->getArgument());
                $this->export(NULL, $this->getParameter('repeat_password_argument_name'));
            }

            $errors = $this->incident ? $this->incident->getErrors() : array();
            $success = (count($errors) === 0);

            if ($success)
            {
                $passwordHandler = new Auth\CryptedPasswordHandler();
                $user->setPasswordHash($passwordHandler->hash($passwordCandidate));
            }
        }
        
        $this->export($user, $this->getParameter('export', 'user'));

        return $success;
    }

    /**
     * @see http://www.php.net/manual/en/regexp.reference.unicode.php for
     *      a reference of the available character properties in PHP regex
     *
     * @param string $candidate string or password to check against configured complexity rules
     * 
     * @return boolean True|FALSE whether string is complex enough according to given complexity rules.
     */
    protected function checkPasswordComplexity(UserDocument $user, $candidate)
    {
        $uppercaseRule = '/\p{Lu}/u'; // Unicode character with property "Upper case letter" -> simplified alternative: '/[A-Z]/'
        $minUppercaseNo = $this->getParameter('min_uppercase_chars', 0);
        if (preg_match_all($uppercaseRule, $candidate, $matches) < $minUppercaseNo)
        {
            $this->throwError(self::ERR_UPPERCASE_TOKENS);
        }

        $lowercaseRule = '/\p{Ll}/u'; // Unicode character with property "Lower case letter" -> simplified alternative: '/[a-z]/'
        $minLowercaseNo = $this->getParameter('min_lowercase_chars', 0);
        if (preg_match_all($lowercaseRule, $candidate, $matches) < $minLowercaseNo)
        {
            $this->throwError(self::ERR_LOWERCASE_TOKENS);
        }

        $numbersRule = '/\p{Nd}/u'; // Unicode character with property "Decimal number" -> simplified alternative: '/[0-9]/' or '/\d/';
        $minNumbersNo = $this->getParameter('min_decimal_numbers', 0);
        if (preg_match_all($numbersRule, $candidate, $matches) < $minNumbersNo)
        {
            $this->throwError(self::ERR_NUMERIC_TOKENS);
        }

        $minLength = $this->getParameter('min_string_length', 6);
        if (mb_strlen($candidate) < $minLength)
        {
            $this->throwError(self::ERR_TOO_SHORT);
        }

        $maxLength = $this->getParameter('max_string_length', 255);
        if (mb_strlen($candidate) > $maxLength)
        {
            $this->throwError(self::ERR_TOO_LONG);
        }

        if ($this->isCommonPassword($candidate))
        {
            $this->throwError(self::ERR_EASY_GUESSABLE);
        }

        if(! $this->checkMinimumUserDistance($user, $candidate))
        {
            $this->throwError(self::ERR_SIMILARITY);
        }
    }

    protected function checkMinimumUserDistance(UserDocument $user, $candidate)
    {
        $fieldNamesForSimilarityCheck = $this->getParameter('argument_names_for_similarity_check', FALSE);

        if ($fieldNamesForSimilarityCheck !== FALSE)
        {
            if (! is_array($fieldNamesForSimilarityCheck)) // single argument name given? => convert to array
            {
                $fieldNamesForSimilarityCheck = array(0 => $fieldNamesForSimilarityCheck);
            }

            foreach ($fieldNamesForSimilarityCheck as $fieldname)
            {
                $value = $user->getValue($fieldname);

                if ($this->isTooSimilar($candidate, $value))
                {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    /**
     * Uses similar_text() and levenshtein() to compute string differences and
     * return TRUE if both strings are too similar according to the thresholds
     * defined as validator parameters ('similarityPercentage_threshold' and
     * 'minimumLevenshteinDistance').
     * 
     * @param string $first
     * @param string $second
     * 
     * @return boolean True if strings are too similar. False otherwise.
     */
    protected function isTooSimilar($first, $second)
    {
        $similarityThreshold = $this->getParameter('similarity_percentage_threshold', 80);
        $minimumLevenshteinDistance = $this->getParameter('minimum_levenshtein_distance', 4);

        if (mb_strtolower($first) === mb_strtolower($second))
        {
            return TRUE;
        }

        $similarCharsCount = similar_text($first, $second, $similarityPercentage);
        $actualLevenshteinDistance = levenshtein($first, $second);

        // check whether too similar or too less different characters
        if (($similarityPercentage >= $similarityThreshold) || ($actualLevenshteinDistance < $minimumLevenshteinDistance))
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Checks whether the given string is included in a configured text file.
     *
     * @param string $pwd password or username or similar candidate to check
     *
     * @return boolean True, if the given string was found in the configured blacklist file. False otherwise
     *
     * @throws FileNotFoundException If configured file is not readable.
     */
    protected function isCommonPassword($pwd)
    {
        $fileName = $this->getParameter('common_passwords_blacklist_file', FALSE);
        if ($fileName === FALSE)
        {
            return FALSE;
        }

        if (! is_readable($fileName))
        {
            throw new FileNotFoundException('File "' . $fileName . '" is not readable.');
        }

        $file = new SplFileObject($fileName);
        foreach ($file as $line)
        {
            if (FALSE !== mb_strpos($line, $pwd)) // TODO: case insensitive comparison switch as parameter?
            {
                return TRUE;
            }
        }

        return FALSE;
    }
}