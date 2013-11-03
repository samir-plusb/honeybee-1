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
    protected $sentence_chunker;

    public function execute(BaseDocument $document)
    {
        $property_map = $this->getConfig()->get('properties');
        $settings = $this->getExcerptSettings();

        $filter_output = array();
        foreach ($property_map as $fieldname => $target_key) {
            $prop_value = $document->getValue($fieldname);
            $filter_output[$target_key] = $this->createExcerptFor($prop_value, $settings);
        }

        return $filter_output;
    }

    protected function getExcerptSettings()
    {
        $settings = array();

        $cfg = $this->getConfig();

        $settings['characters'] = $this->getConfig()->get('characters');
        $settings['words'] = $this->getConfig()->get('words');
        $settings['sentences'] = $this->getConfig()->get('sentences');
        $settings['skip_sentences'] = $this->getConfig()->get('skip_sentences', 0);
        $settings['paragraphs'] = $this->getConfig()->get('paragraphs');

        $settings['skip'] = $cfg->get('skip');

        $settings['ellipsis'] = $cfg->get('ellipsis');
        $settings['append_ellipsis'] = $cfg->get('append_ellipsis');

        $settings['strip_tags'] = $cfg->get('strip_tags');
        $settings['allowed_tags'] = $cfg->get('allowed_tags');

        $settings['strip_newlines'] = $cfg->get('strip_newlines');
        $settings['strip_tabs'] = $cfg->get('strip_tabs');
        $settings['strip_excessive_whitespace'] = $cfg->get('strip_excessive_whitespace');
        $settings['only_full_sentences'] = $cfg->get('only_full_sentences');

        return $settings;
    }

    protected function createExcerptFor($text, $settings)
    {
        $append_ellipsis = isset($settings['append_ellipsis']) ? $settings['append_ellipsis'] : true;
        $ellipsis = isset($settings['ellipsis']) ? $settings['ellipsis'] : ' […]';
        $skip = isset($settings['skip']) ? $settings['skip'] : 0;
        $allowed_tags = isset($settings['allowed_tags']) ? $settings['allowed_tags'] : '';
        $strip_tags = isset($settings['strip_tags']) ? $settings['strip_tags'] : true;
        $strip_newlines = isset($settings['strip_newlines']) ? $settings['strip_newlines'] : true;
        $strip_tabs = isset($settings['strip_tabs']) ? $settings['strip_tabs'] : true;
        $strip_excessive_whitespace = isset($settings['strip_excessive_whitespace']) ? $settings['strip_excessive_whitespace'] : true;
        $only_full_sentences = isset($settings['only_full_sentences']) ? $settings['only_full_sentences'] : false;

        $max_characters = isset($settings['characters']) ? $settings['characters'] : 0;
        $max_words = isset($settings['words']) ? $settings['words'] : 0;
        $max_sentences = isset($settings['sentences']) ? $settings['sentences'] : 0;
        $max_paragraphs = isset($settings['paragraphs']) ? $settings['paragraphs'] : 0;

        $excerpt = trim($text);
        if ($strip_newlines) {
            $excerpt =  preg_replace('#\n|\r|\r\n|\n\r#', '', $excerpt);
        }
        if ($strip_tabs) {
            $excerpt =  preg_replace('#\t#', '', $excerpt);
        }
        if ($strip_excessive_whitespace) {
            $excerpt =  preg_replace('#\s{2,}#', ' ', $excerpt);
        }
        if ($strip_tags) {
            if ($max_paragraphs) {
                $allowed_tags .= '<p>';
            }
            $excerpt = preg_replace('/(<\/[^>]+?>)(<[^>\/][^>]*?>)/', '$1 $2', $excerpt);
            $excerpt = strip_tags($excerpt, $allowed_tags);
            $excerpt =  preg_replace('#\s{2,}#', ' ', $excerpt);
        }

        $character_count = mb_strlen($excerpt);
        $word_count = count(explode(" ", $excerpt));
        $paragraph_count = count(explode("</p>", $excerpt));

        if (!$max_characters && !$max_words && !$max_sentences && !$max_paragraphs) {
            $max_characters = 250; // magic default in case of no settings at all
        }

        $create_excerpt = false;
        $create_excerpt = ($max_characters) ? ($max_characters < $character_count) ? true : false : $create_excerpt;
        $create_excerpt = ($max_words) ? ($max_words < $word_count) ? true : false : $create_excerpt;
        $create_excerpt = ($max_paragraphs) ? ($max_paragraphs < $paragraph_count) ? true : false : $create_excerpt;

        if (!$create_excerpt) {
            return $excerpt; // nothing to excerpt, return immediately
        }

        if ($max_characters) { // cut to maximum number of characters
            if ($only_full_sentences) {  // then cut to maximum number of sentences
                if ($excerpt_sentences = $this->extractSentences(
                    $this->extractCharacters($excerpt, $settings),
                    $settings
                )) {
                    $excerpt = $excerpt_sentences;
                } else { // or take the first sentence, if there are no full sentences with the character range
                    $settings['sentences'] = 1;
                    $excerpt = $this->extractSentences($excerpt, $settings);
                }
            }
        } else if ($max_words) { // cut to maximum number of words
            $excerpt = $this->extractWords($excerpt, $settings);
        } else if ($max_sentences) { // cut to maximum number of sentences
            $excerpt = $this->extractSentences($excerpt, $settings);
        } else if ($max_paragraphs) { // cut to maximum number of paragraphs
            $excerpt = $this->extractParagraphs($excerpt, $settings);
        }

        if ($append_ellipsis) {
            $excerpt = preg_replace('#\W+$#', '', $excerpt);
            if ($max_paragraphs) {
                $excerpt .= '>';
            }
            $excerpt .= $ellipsis;
        }

        return $excerpt;
    }

    protected function extractSentences($text, array $settings)
    {
        $sentence_chunker = $this->getSenctenceChunker();
        $excerpt_sentences = $sentence_chunker->chunk($text);
        if ($excerpt_sentences && count($excerpt_sentences) > 0) {
            $excerpt_sentences = array_slice(
                $excerpt_sentences,
                $settings['skip_sentences'],
                $settings['sentences']
            );
            return implode(' ', $excerpt_sentences);
        } else {
            return false;
        }
    }

    protected function extractCharacters($text, array $settings)
    {
        $skip = isset($settings['skip']) ? $settings['skip'] : 0;
        $max_characters = isset($settings['characters']) ? $settings['characters'] : 0;

        $text = mb_substr($text, $skip, $max_characters);

        return mb_substr($text, 0, strrpos($text," "));
    }

    protected function extractWords($text, array $settings)
    {
        $skip = isset($settings['skip']) ? $settings['skip'] : 0;
        $max_words = isset($settings['words']) ? $settings['words'] : 0;

        return implode(' ', array_slice(explode(' ', $excerpt), $skip, $max_words));
    }

    protected function extractParagraphs($text, array $settings)
    {
        $skip = isset($settings['skip']) ? $settings['skip'] : 0;
        $max_paragraphs = isset($settings['paragraphs']) ? $settings['paragraphs'] : 0;

        $excerpt = implode('</p>', array_slice(explode('</p>', $excerpt), $skip, $max_paragraphs)) . '</p>';
        $excerpt = preg_replace('#^(.*?)<p#', '<p', $excerpt); // strip content before first paragraph

        return preg_replace('#</p>(.*?)<p#', '</p><p', $excerpt); // strip content between paragraphs
    }

    protected function getSenctenceChunker()
    {
        if (!$this->sentence_chunker) {
            $this->sentence_chunker = new SentenceChunker(
                new ArrayConfig(
                    array('dot_tokens_file' => realpath($this->getConfig()->get('dot_tokens_file')))
                )
            );
        }

        return $this->sentence_chunker;
    }
}
