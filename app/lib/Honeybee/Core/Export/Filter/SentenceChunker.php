<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Config\IConfig;

/**
 * The SentenceChunker splits text into an array of sentences.
 */
class SentenceChunker
{
    const MIN_SENTENCE_LENGTH = 3;

    protected $config;

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

        $sentence_end_offset = $this->findEndOfSentence($text);
        while ($sentence_end_offset !== false) {
            $sentences[] = trim(mb_strcut($text, 0, $sentence_end_offset));
            $text = trim(mb_strcut($text, $sentence_end_offset + 1));
            $sentence_end_offset = $this->findEndOfSentence($text);
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
    protected function findEndOfSentence($text)
    {
        if (strlen($text) <= self::MIN_SENTENCE_LENGTH) {
            return false;
        }
        $sentence_end = $this->findDelimitingDot($text);

        $next_question_mark = (int)mb_strpos($text, '?');
        if (
            (0 !== $next_question_mark && $sentence_end > $next_question_mark)
            || 0 === $sentence_end
        ) { // check if there is a question mark before the first dot.
            $sentence_end = $next_question_mark;
        }

        $next_exlamation_mark = (int)mb_strpos($text, '!');
        if (
            (0 !== $next_exlamation_mark && $sentence_end > $next_exlamation_mark)
            || 0 === $sentence_end
        ) { // check if there is a exlamtion mark before the first dot or question mark.
            $sentence_end = $next_exlamation_mark;
        }

        return $sentence_end > 0 ? $sentence_end + 1 : false;
    }

    /**
     * Search for the offset of the first dot that terminates a sentence within the given text.
     * Returns false if no valid dot token is found.
     *
     * @param string $text
     *
     * @return int | false
     */
    protected function findDelimitingDot($text)
    {
        $invalid_dot_regex = sprintf(
            '~(%s)\.$~',
            implode('|', $this->config->get('dot_context_tokens', array()))
        );

        $is_valid_dot_position = false;
        $next_dot_position = (int)mb_strpos($text, '.');
        while (!$is_valid_dot_position && $next_dot_position > 0) {
            if ($next_dot_position > 0) {
                $potential_sentence = mb_substr($text, 0, $next_dot_position + 1);
                if (preg_match($invalid_dot_regex, $potential_sentence)) {
                    $next_dot_position = (int)mb_strpos($text, '.', $next_dot_position + 1);
                } else {
                    $is_valid_dot_position = true;
                }
            } else {
                $next_dot_position = 0;
            }
        }

        return $next_dot_position;
    }
}
