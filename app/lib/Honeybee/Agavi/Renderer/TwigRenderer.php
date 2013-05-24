<?php

namespace Honeybee\Agavi\Renderer;

/**
 * Extends the AgaviTwigRenderer to add twig extensions via parameters. If you
 * need more functionality you should extend the AgaviTwigRenderer by yourself
 * and use that in the output_types.xml file.
 *
 * @author Jan Schütze <jans@dracoblue.de>
 * @author Steffen Gransow <graste@mivesto.de>
 */
class TwigRenderer extends \AgaviTwigRenderer
{
    protected function getEngine()
    {
        $twig = parent::getEngine();

        foreach ($this->getParameter('extensions', array()) as $extension_class_name)
        {
            $ext = new $extension_class_name();
            // as the renderer is reusable it may have the extension already
            if (!$twig->hasExtension($ext->getName()))
            {
                $twig->addExtension($ext);
            }
        }

        return $twig;
    }
}

