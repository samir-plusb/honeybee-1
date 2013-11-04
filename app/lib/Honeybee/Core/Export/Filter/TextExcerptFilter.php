<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\BaseDocument;
use Honeybee\Core\Config\IConfig;
use Honeybee\Core\Config\ArrayConfig;

/**
 * Creates excerpts from (rich) text properties. The following settings are
 * supported at the moment.
 *
 * - characters: maximum number of characters (default: 250)
 * - words: maximum number of words
 * - sentences: maximum number of sentences (ending with ".", "?" or "!")
 * - paragraphs: maximum number of paragraphs (actual html "</p>"s)
 * - skip: number of characters|words|sentences|paragraphs to skip at the
 *         beginning (strip from start)
 * - ellipsis: string to use as an ellipsis (default is: " […]")
 * - append_ellipsis: whether to append the ellipsis or not (default: true)
 * - strip_tags: whether to strip html tags before excerpting (default: true)
 * - allowed_tags: tags not to strip if "strip_tags" is used (format: "<a><p>")
 * - strip_newlines: whether to strip "\n", "\r" and "\r\n" (default: true)
 * - strip_tabs: whether to strip tab characters (default: true)
 * - strip_excessive_whitespace: strip duplicate whitespaces? (default: true)
 *
 * Please note, that not all setting combinations are supported and that one
 * can either cut characters, words, sentences OR paragraphs. A mix is not
 * supported atm.
 */
class TextExcerptFilter extends BaseFilter
{
    protected $text_service;

    public function execute(BaseDocument $document)
    {
        $property_map = $this->getConfig()->get('properties');

        $filter_output = array();
        foreach ($property_map as $fieldname => $target_key) {
            $text = $document->getValue($fieldname);
            $filter_output[$target_key] = $this->createExcerptFor($text);
        }

        return $filter_output;
    }

    protected function createExcerptFor($text)
    {
        $config = $this->getConfig();

        $max_characters = $config->get('characters', 0);
        $max_words = $config->get('words', 0);
        $max_sentences = $config->get('sentences', 0);
        $max_paragraphs = $config->get('paragraphs', 0);
        if (!$max_characters && !$max_words && !$max_sentences && !$max_paragraphs) {
            $max_characters = 250; // magic default in case of no settings at all
        }

        $excerpt = $this->stripStuff($text);

        $character_count = mb_strlen($excerpt);
        $word_count = count(explode(" ", $excerpt));
        $paragraph_count = count(explode("</p>", $excerpt));
        // check if we must really create the excerpt or if we can skip it
        $create_excerpt = false;
        $create_excerpt = ($max_characters) ? ($max_characters < $character_count) ? true : false : $create_excerpt;
        $create_excerpt = ($max_words) ? ($max_words < $word_count) ? true : false : $create_excerpt;
        $create_excerpt = ($max_paragraphs) ? ($max_paragraphs < $paragraph_count) ? true : false : $create_excerpt;
        if (!$create_excerpt) { // nothing to excerpt, return immediately
            return $excerpt;
        }

        if ($max_characters) { // cut to maximum number of characters
            if ($config->get('only_full_sentences', false)) { // then cut to maximum number of sentences
                if ($excerpt_sentences = $this->extractSentences(
                    $this->extractCharacters($excerpt)
                )) {
                    $excerpt = $excerpt_sentences;
                } else { // or take the first sentence, if there are no full sentences with the character range
                    $excerpt = $this->extractSentences($excerpt, 1);
                }
            }
        } else if ($max_words) { // cut to maximum number of words
            $excerpt = $this->extractWords($excerpt);
        } else if ($max_sentences) { // cut to maximum number of sentences
            $excerpt = $this->extractSentences($excerpt);
        } else if ($max_paragraphs) { // cut to maximum number of paragraphs
            $excerpt = $this->extractParagraphs($excerpt);
        }

        if ($config->get('append_ellipsis', true)) {
            $excerpt = preg_replace('#\W+$#', '', $excerpt);
            if ($max_paragraphs) {
                $excerpt .= '>';
            }
            $excerpt .= $config->get('ellipsis', ' […]');
        }

        return $excerpt;
    }

    protected function stripStuff($text)
    {
        $text_service = $this->getTextService();

        $text = trim($text);
        if ($this->config->get('strip_newlines', true)) {
            $text =  $text_service->stripNewlines($text);
        }
        if ($this->config->get('strip_tabs', true)) {
            $text =  $text_service->stripTabs($text);
        }
        if ($this->config->get('strip_excessive_whitespace', true)) {
            $text =  $text_service->stripMultipleSpaces($text);
        }
        if ($this->config->get('strip_tags', true)) {
            $allowed_tags = $this->config->get('allowed_tags', '');
            if ($this->config->get('paragraphs', false)) {
                $allowed_tags .= '<p>';
            }
            $text = $text_service->stripTags($text, $allowed_tags);
        }

        return $text;
    }

    protected function extractSentences($text, $count = 0)
    {
        if ($count === 0) {
            $count = $this->config->get('sentences', 0);
        }
        $offset = $this->config->get('skip_sentences', 0);

        $excerpt_sentences = $this->getTextService()->getSentences($text, $count, $offset);
        if ($excerpt_sentences && count($excerpt_sentences) > 0) {
            return implode(' ', $excerpt_sentences);
        } else {
            return false;
        }
    }

    protected function extractCharacters($text, $count = 0)
    {
        if ($count === 0) {
            $count = $this->config->get('characters', 0);
        }
        $offset = $this->config->get('skip', 0);
        return $this->getTextService()->getCharacters($text, $count, $offset);
    }

    protected function extractWords($text, $count = 0)
    {
        if ($count === 0) {
            $count = $this->config->get('words', 0);
        }
        $offset = $this->config->get('skip', 0);
        return $this->getTextService()->getWords($text, $count, $offset);
    }

    protected function extractParagraphs($text, $count = 0)
    {
        if ($count === 0) {
            $count = $this->config->get('paragraphs', 0);
        }
        $offset = $this->config->get('skip', 0);
        return $this->getTextService()->getWords($text, $count, $offset);
    }

    protected function getTextService()
    {
        if (!$this->text_service) {
            $dot_tokens_file = realpath($this->config->get('dot_tokens_file'));
            $this->text_service = new TextService(
                new ArrayConfig(array('dot_tokens_file' => $dot_tokens_file))
            );
        }

        return $this->text_service;
    }
}
