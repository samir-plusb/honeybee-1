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
        $filterOutput = array();

        $propertyMap = $this->getConfig()->get('properties');
        $module = $document->getModule();

        $settings = $this->getExcerptSettings();

        foreach ($propertyMap as $fieldname => $targetKey)
        {
            //$field = $module->getField($fieldname);
            $propValue = $document->getValue($fieldname);
            $filterOutput[$targetKey] = $this->createExcerptFor($propValue, $settings);
        }

        return $filterOutput;
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
        $appendEllipsis = isset($settings['append_ellipsis']) ? $settings['append_ellipsis'] : TRUE;
        $ellipsis = isset($settings['ellipsis']) ? $settings['ellipsis'] : ' […]';
        $skip = isset($settings['skip']) ? $settings['skip'] : 0;
        $allowedTags = isset($settings['allowed_tags']) ? $settings['allowed_tags'] : '';
        $stripTags = isset($settings['strip_tags']) ? $settings['strip_tags'] : TRUE;
        $stripNewlines = isset($settings['strip_newlines']) ? $settings['strip_newlines'] : TRUE;
        $stripTabs = isset($settings['strip_tabs']) ? $settings['strip_tabs'] : TRUE;
        $stripExcessiveWhitespace = isset($settings['strip_excessive_whitespace']) ? $settings['strip_excessive_whitespace'] : TRUE;
        $onlyFullSentences = isset($settings['only_full_sentences']) ? $settings['only_full_sentences'] : FALSE;

        $maxCharacters = isset($settings['characters']) ? $settings['characters'] : 0;
        $maxWords = isset($settings['words']) ? $settings['words'] : 0;
        $maxSentences = isset($settings['sentences']) ? $settings['sentences'] : 0;
        $maxParagraphs = isset($settings['paragraphs']) ? $settings['paragraphs'] : 0;

        $excerpt = trim($text);

        if ($stripNewlines)
        {
            $excerpt =  preg_replace('#\n|\r|\r\n|\n\r#', '', $excerpt);
        }

        if ($stripTabs)
        {
            $excerpt =  preg_replace('#\t#', '', $excerpt);
        }

        if ($stripExcessiveWhitespace)
        {
            $excerpt =  preg_replace('#\s{2,}#', ' ', $excerpt);
        }

        if ($stripTags)
        {
            if ($maxParagraphs)
            {
                $allowedTags .= '<p>';
            }
            $excerpt = preg_replace('/(<\/[^>]+?>)(<[^>\/][^>]*?>)/', '$1 $2', $excerpt);
            $excerpt = strip_tags($excerpt, $allowedTags);
            $excerpt =  preg_replace('#\s{2,}#', ' ', $excerpt);
        }

        $characterCount = mb_strlen($excerpt);
        $wordCount = count(explode(" ", $excerpt));
        $sentenceCount = count(explode(".", $excerpt));
        $paragraphCount = count(explode("</p>", $excerpt));

        if (!$maxCharacters && !$maxWords && !$maxSentences && !$maxParagraphs)
        {
            $maxCharacters = 250; // magic default in case of no settings at all
        }

        $createExcerpt = FALSE;
        $createExcerpt = ($maxCharacters) ? ($maxCharacters < $characterCount) ? TRUE : FALSE : $createExcerpt;
        $createExcerpt = ($maxWords) ? ($maxWords < $wordCount) ? TRUE : FALSE : $createExcerpt;
        $createExcerpt = ($maxSentences) ? ($maxSentences < $sentenceCount) ? TRUE : FALSE : $createExcerpt;
        $createExcerpt = ($maxParagraphs) ? ($maxParagraphs < $paragraphCount) ? TRUE : FALSE : $createExcerpt;

        if (!$createExcerpt)
        {
            return $excerpt; // nothing to excerpt, return immediately
        }

        if ($maxCharacters) // cut to maximum number of characters
        {
            $excerpt = $this->extractCharacters($excerpt, $settings);
            if ($onlyFullSentences)
            {
                $excerpt = $this->extractSentences($excerpt, $settings);
            }
        }
        else if ($maxWords) // cut to maximum number of words
        {
            $excerpt = $this->extractWords($excerpt, $settings);
        }
        else if ($maxSentences) // cut to maximum number of sentences
        {
            $excerpt = $this->extractSentences($excerpt, $settings);
        }
        else if ($maxParagraphs) // cut to maximum number of paragraphs
        {
            $excerpt = $this->extractParagraphs($excerpt, $settings);
        }

        if ($appendEllipsis)
        {
            $excerpt = preg_replace('#\W+$#', '', $excerpt);
            if ($maxParagraphs)
            {
                $excerpt .= '>';
            }

            $excerpt .= $ellipsis;
        }

        return $excerpt;
    }

    protected function extractSentences($text, array $settings)
    {
        $skip = isset($settings['skip']) ? $settings['skip'] : 0;
        $maxSentences = isset($settings['sentences']) ? $settings['sentences'] : 0;

        // remove sentences that should be skipped
        $regex = '!^((?:.*?[\.\!\?](?=\s+)){' . $skip . '})!sm';
        $text = preg_replace($regex, '', $text);
        // get number of sentences that we need
        $regex = '~^((?:.*?[\.\!\?](?= )){' . $maxSentences . '})~sm';

        if (preg_match_all($regex, $text, $matches))
        {
            $text = $matches[0][0];
        }
        else
        {
            // First remove unwanted spaces - not needed really
            $text = str_replace(" .",".",$text);
            $text = str_replace(" ?","?",$text);
            $text = str_replace(" !","!",$text);
            // Find periods, exclamation- or questionmarks with a word before but not after.
            // Perfect if you only need/want to return the first sentence of a paragraph.
            preg_match('/^.*[^\s](\.|\?|\!)/', $text, $matches);
            $text = $matches[0];
            // fallback, probably not necessary as there are no sentences left anyways...
            //$text = implode('. ', array_slice(explode('.', $text), $skip, $maxSentences)) . '.';
            //$text = preg_replace('#\s\s+#', ' ', $text);
        }

        return $text;
    }

    protected function extractCharacters($text, array $settings)
    {
        $skip = isset($settings['skip']) ? $settings['skip'] : 0;
        $maxCharacters = isset($settings['characters']) ? $settings['characters'] : 0;

        $text = mb_substr($text, $skip, $maxCharacters);

        return mb_substr($text, 0, strrpos($text," "));
    }

    protected function extractWords($text, array $settings)
    {
        $skip = isset($settings['skip']) ? $settings['skip'] : 0;
        $maxWords = isset($settings['words']) ? $settings['words'] : 0;

        return implode(' ', array_slice(explode(' ', $excerpt), $skip, $maxWords));
    }

    protected function extractParagraphs($text, array $settings)
    {
        $skip = isset($settings['skip']) ? $settings['skip'] : 0;
        $maxParagraphs = isset($settings['paragraphs']) ? $settings['paragraphs'] : 0;

        $excerpt = implode('</p>', array_slice(explode('</p>', $excerpt), $skip, $maxParagraphs)) . '</p>';
        $excerpt = preg_replace('#^(.*?)<p#', '<p', $excerpt); // strip content before first paragraph

        return preg_replace('#</p>(.*?)<p#', '</p><p', $excerpt); // strip content between paragraphs
    }
}
