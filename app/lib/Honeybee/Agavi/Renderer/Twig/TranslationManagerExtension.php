<?php
namespace Honeybee\Agavi\Renderer\Twig;

/**
 * Twig extension to have AgaviTranslationManager methods available as simple
 * and short functions in twig templates. This should save some keystrokes.
 *
 * @author Jan Schütze <jans@dracoblue.de>
 * @author Steffen Gransow <graste@mivesto.de>
 */
class TranslationManagerExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            '_' => new \Twig_Function_Method($this, '_'),
            '_c' => new \Twig_Function_Method($this, '_c'),
            '_n' => new \Twig_Function_Method($this, '_n'),
            '_d' => new \Twig_Function_Method($this, '_d'),
            '__' => new \Twig_Function_Method($this, '__')
        );
    }

    /**
     * Translate a message into the current locale.
     *
     * @param mixed $message message to be translated.
     * @param string $domain domain in which the translation should be done.
     * @param \AgaviLocale $locale locale which should be used for formatting.
     * @param array $parameters parameters which should be used for sprintf on the translated string.
     *
     * @return string translated message.
     */
    public function _($message, $domain = null, $locale = null, array $parameters = null)
    {
        $tm = \AgaviContext::getInstance()->getTranslationManager();
        return $tm->_($message, $domain, $locale, $parameters);
    }

    /**
     * Translate a singular/plural message into the current locale.
     *
     * @param string $singularMessage message for the singular form.
     * @param string $pluralMessage message for the plural form.
     * @param int $amount amount for which the translation should be done.
     * @param string $domain domain in which the translation should be done.
     * @param \AgaviLocale $locale locale which should be used for formatting.
     * @param array $parameters parameters which should be used for sprintf on the translated string.
     *
     * @return string translated message.
     */
    public function __($singularMessage, $pluralMessage, $amount, $domain = null, $locale = null, array $parameters = null)
    {
        $tm = \AgaviContext::getInstance()->getTranslationManager();
        return $tm->__($singularMessage, $pluralMessage, $amount, $domain, $locale, $parameters);
    }

    /**
     * Formats a date in the current locale.
     *
     * @param mixed $date date to be formatted.
     * @param string $domain domain in which the date should be formatted.
     * @param \AgaviLocale $locale locale which should be used for formatting.
     *
     * @return string formatted date.
     */
    public function _d($date, $domain = null, $locale = null)
    {
        $tm = \AgaviContext::getInstance()->getTranslationManager();
        return $tm->_d($date, $domain, $locale);
    }

    /**
     * Formats a currency amount in the current locale.
     *
     * @param mixed $number number to be formatted.
     * @param string $domain domain in which the amount should be formatted.
     * @param \AgaviLocale $locale locale which should be used for formatting.
     *
     * @return string formatted number.
     */
    public function _c($number, $domain = null, $locale = null)
    {
        $tm = \AgaviContext::getInstance()->getTranslationManager();
        return $tm->_c($number, $domain, $locale);
    }

    /**
     * Formats a number in the current locale.
     *
     * @param mixed $number number to be formatted.
     * @param string $domain domain in which the number should be formatted.
     * @param \AgaviLocale $locale locale which should be used for formatting.
     *
     * @return string formatted number.
     */
    public function _n($number, $domain = null, $locale = null)
    {
        $tm = \AgaviContext::getInstance()->getTranslationManager();
        return $tm->_n($number, $domain, $locale);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string extension name.
     */
    public function getName()
    {
        return 'TranslationManager';
    }
}

