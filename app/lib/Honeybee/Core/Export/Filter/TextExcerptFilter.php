<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\BaseDocument;

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
        $sentence_count = count(explode(".", $excerpt));
        $paragraph_count = count(explode("</p>", $excerpt));

        if (!$max_characters && !$max_words && !$max_sentences && !$max_paragraphs) {
            $max_characters = 250; // magic default in case of no settings at all
        }

        $create_excerpt = false;
        $create_excerpt = ($max_characters) ? ($max_characters < $character_count) ? true : false : $create_excerpt;
        $create_excerpt = ($max_words) ? ($max_words < $word_count) ? true : false : $create_excerpt;
        $create_excerpt = ($max_sentences) ? ($max_sentences < $sentence_count) ? true : false : $create_excerpt;
        $create_excerpt = ($max_paragraphs) ? ($max_paragraphs < $paragraph_count) ? true : false : $create_excerpt;

        if (!$create_excerpt) {
            return $excerpt; // nothing to excerpt, return immediately
        }

        if ($max_characters) { // cut to maximum number of characters
            if ($only_full_sentences) {
                if (!($excerpt_sentences = $this->extractSentences($this->extractCharacters($excerpt, $settings), $settings))) {
                    // no full sentences within the given excerpt,
                    // try to find the first full sentence ...
                    $first_sentence_end = min(
                        mb_strpos($excerpt, '.'),
                        mb_strpos($excerpt, '?'),
                        mb_strpos($excerpt, '!')
                    );
                    $excerpt = mb_substr($excerpt, 0, $first_sentence_end + 1);
                } else {
                    $excerpt = $excerpt_sentences;
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
        $skip = isset($settings['skip']) ? $settings['skip'] : 0;
        $max_sentences = isset($settings['sentences']) ? $settings['sentences'] : 0;

        // remove sentences that should be skipped
        $regex = '!^((?:.*?[\.\!\?](?=\s+)){' . $skip . '})!sm';
        $text = preg_replace($regex, '', $text);
        // get number of sentences that we need
        $regex = '~^((?:.*?[\.\!\?](?= )){' . $max_sentences . '})~sm';

        if (preg_match_all($regex, $text, $matches)) {
            $text = $matches[0][0];
        } else {
            // First remove unwanted spaces - not needed really
            $text = str_replace(" .",".",$text);
            $text = str_replace(" ?","?",$text);
            $text = str_replace(" !","!",$text);
            // Find periods, exclamation- or questionmarks with a word before but not after.
            // Perfect if you only need/want to return the first sentence of a paragraph.
            if (preg_match('/^.*[^\s](\.|\?|\!)/', $text, $matches)) {
                $text = $matches[0];
            } else {
                return false;
            }

            // fallback, probably not necessary as there are no sentences left anyways...
            //$text = implode('. ', array_slice(explode('.', $text), $skip, $max_sentences)) . '.';
            //$text = preg_replace('#\s\s+#', ' ', $text);
        }

        return $text;
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
}
