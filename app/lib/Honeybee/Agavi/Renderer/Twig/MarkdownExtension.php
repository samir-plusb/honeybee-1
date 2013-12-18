<?php

namespace Honeybee\Agavi\Renderer\Twig;

use \Michelf\Markdown;

class MarkdownExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'markdown' => new \Twig_Function_Method($this, 'markdown')
        );
    }

    public function markdown($markdown_text)
    {
        return Markdown::defaultTransform($markdown_text);
    }

    public function getName()
    {
        return 'MarkdownConverter';
    }
}
