<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Config\IConfig;
use RuntimeException;

/**
 * The SentenceChunker splits text into an array of sentences.
 */
class SentenceChunker
{
    const MIN_SENTENCE_LENGTH = 3;

    protected $config;

    protected $dot_context_tokens;

    public function __construct(IConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Splits a given text into an array of sentences.
     *
     * @param string $text
     *
     * @return array
     */
    public function chunk($text)
    {
        $sentences = array();

        $sentence_end_offset = $this->findEndOfFirstSentence($text);
        while ($sentence_end_offset !== false) {
            $sentence_end_offset += 1;
            $sentences[] = trim(mb_substr($text, 0, $sentence_end_offset));
            $text = trim(mb_substr($text, $sentence_end_offset));
            $sentence_end_offset = $this->findEndOfFirstSentence($text);
        }

        return (count($sentences) > 0) ? $sentences : false;
    }

    /**
     * Returns the offset where the first sentence of the given text ends.
     * Returns false if no sentence is found.
     *
     * @param string $text
     *
     * @return int | false
     */
    public function findEndOfFirstSentence($text)
    {
        if (strlen($text) <= self::MIN_SENTENCE_LENGTH) {
            return false;
        }
        $sentence_end = $this->findFirstSentenceDelimiterOffset($text);

        return $sentence_end > 0 ? $sentence_end : false;
    }

    public function findFirstSentenceDelimiterOffset($text)
    {
        $period_offset = (int)$this->findFirstDelimitingPeriod($text);
        $exlamation_mark_offset = (int)$this->findFirstDelimitingExclamationMark($text);
        $question_mark_offset = (int)$this->findFirstDelimitingQuestionMark($text);

        $values = array_filter(
            array($period_offset, $exlamation_mark_offset, $question_mark_offset)
        );
        sort($values);

        return empty($values) ? false : min($values);
    }

    public function findFirstDelimitingPeriod($text)
    {
        $invalid_period_regex = sprintf(
            '~(?<!\w)(%s)$~is',
            implode('|', $this->getDotContextTokens())
        );

        $next_period_offset = (int)mb_strpos($text, '.');
        $is_valid_period_offset = preg_match($invalid_period_regex, $text);

        while (!$is_valid_period_offset && $next_period_offset > 0) {
            $potential_sentence = substr($text, 0, $next_period_offset + 1);
            if (preg_match($invalid_period_regex, $potential_sentence)) {
                $next_period_offset = (int)mb_strpos($text, '.', $next_period_offset + 1);
            } else {
                $is_valid_period_offset = true;
            }
        }

        return $next_period_offset;
    }

    public function findFirstDelimitingExclamationMark($text)
    {
        return (int)mb_strpos($text, '!');
    }

    public function findFirstDelimitingQuestionMark($text)
    {
        return (int)mb_strpos($text, '?');
    }

    protected function getDotContextTokens()
    {
        if (!$this->dot_context_tokens) {
            $this->loadDotContextTokens();
        }

        return $this->dot_context_tokens;
    }

    /**
     * Load our list of non-sentence-delimiting dot-occurences and prepare
     * them as pattern for matching against the end of our potential sentences.
     */
    protected function loadDotContextTokens()
    {
        $this->dot_context_tokens = array();
        $dot_tokens_file = $this->config->get('dot_tokens_file', false);

        if ($dot_tokens_file) {
            if (is_readable($dot_tokens_file)) {
                foreach (file($dot_tokens_file) as $token) {
                    $this->dot_context_tokens[] = trim(str_replace('.', '\.', $token));
                }
            } else {
                throw new RuntimeException("Unable to load dot-tokens at location: " . $dot_tokens_file);
            }
        }
    }
}
