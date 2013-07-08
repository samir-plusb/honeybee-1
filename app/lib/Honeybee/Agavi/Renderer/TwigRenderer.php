<?php

namespace Honeybee\Agavi\Renderer;

//use Honeybee\Agavi\Logging;

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

    /**
     * Return an initialized Twig instance.
     *
     * @return Twig_Environment
     */
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

    /**
     * Render the presentation and return the result. This expands
     * the default behaviour of the original Agavi method by
     * discarding non-existant paths silently (as Twig doesn't
     * like non-existing directories).
     *
     * Lookup is as follows:
     *
     * 1. paths from 'template_dirs' parameter (or 'core.template_dir)
     * 2. path to the directory the current template is in
     * 3. path to the module's template directory (via 'agavi.template.directory' parameter from the module's module.xml)
     *
     * @param \AgaviTemplateLayer $layer template layer to render
     * @param array $attributes template variables
     * @param array $slots slots
     * @param array $moreAssigns associative array of additional assigns
     *
     * @return string rendered result
     */
    public function render(\AgaviTemplateLayer $layer, array &$attributes = array(), array &$slots = array(), array &$moreAssigns = array())
    {
        $twig = $this->getEngine();
        $template_dirs = (array) $this->getParameter('template_dirs', array(\AgaviConfig::get('core.template_dir')));
        $path = $layer->getResourceStreamIdentifier();

        if ($layer instanceof \AgaviFileTemplateLayer)
        {
            $paths = array();
            // allow loading from the main project template dir by default (and any other directories the user has set through configuration)
            foreach ($template_dirs as $dir)
            {
                // replace e.g. {module} with name of the current module if possible
                $dir = \AgaviToolkit::expandVariables($dir, array_merge(array_filter($layer->getParameters(), 'is_scalar'), array_filter($layer->getParameters(), 'is_null')));
                if (is_dir($dir) && is_readable($dir))
                {
                    $paths[] = $dir;
                }
                else
                {
                    //\AgaviContext::getInstance()->getLoggerManager()->logTo(null, Logging\Logger::INFO, __METHOD__, "Template directory $dir does not exist or is not readable. Check 'core.template_dir' setting or the TwigRenderer's 'template_dirs' parameter or create the directory.");
                }
            }

            // set the directory the template is in as the first path to load from, and the directory set on the layer second
            // that way, including another template inside this template will look at e.g. a locale subdirectory first before falling back to the originally defined folder
            $pathinfo = pathinfo($path);
            $paths[] = $pathinfo['dirname'];
            $paths[] = $layer->getParameter('directory');

            $twig->setLoader(new \Twig_Loader_Filesystem($paths));
            $source = $pathinfo['basename'];
        }
        else
        {
            // a stream template or whatever; either way, it's something Twig can't load directly :S
            $twig->setLoader(new \Twig_Loader_String());
            $source = file_get_contents($path);
        }

        $template = $twig->loadTemplate($source);

        $data = array();

        // template vars
        if ($this->extractVars)
        {
            foreach ($attributes as $name => $value)
            {
                $data[$name] = $value;
            }
        }
        else
        {
            $data[$this->varName] = $attributes;
        }

        // slots
        $data[$this->slotsVarName] = $slots;

        // dynamic assigns (global ones were set in getEngine())
        $finalMoreAssigns = self::buildMoreAssigns($moreAssigns, $this->moreAssignNames);
        foreach ($finalMoreAssigns as $key => $value)
        {
            $data[$key] = $value;
        }

        return $template->render($data);
    }

}

