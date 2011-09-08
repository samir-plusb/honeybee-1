<?php

/**
 * The ResourceBasedConfig class is an abstract implementation of the ImportBaseConfig base.
 * It's job is to provide a strategy for loading arbitary resource types that reflect configuration state.
 * This is implemented by taking uri's as a config source and implementing the IUriContainer
 * interface to handle them.
 * Every IImportConfig that gets it's settings from some kind of resource,
 * rather direct data should extend this class.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
abstract class ResourceBasedConfig extends ImportBaseConfig implements IUriContainer
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds our original uri (config source).
     *
     * @var         string
     */
    private $configUri;

    /**
     * Holds our parsed uri parts,
     *
     * @var         array
     */
    private $configUriParts;

    // ---------------------------------- </MEMBERS> ---------------------------------------------
    // ---------------------------------- <ABSTRACT METHODS> -------------------------------------

    /**
     * The loadResource method is responseable for actually loading and parsing
     * a given resource into an array, that we can use as the base for our settings.
     * Usually you would want to use our {@see ResourceBasedConfig::getUriParts()} and then pull out
     * the path or whatever you need to load the thing.
     *
     * @return      array
     */
    abstract protected function loadResource();

    // ---------------------------------- </ABSTRACT METHODS> ------------------------------------
    // ---------------------------------- <IUriContainer METHODS> --------------------------------

    /**
     * Return the uri parts, that reflect our config source (uri).
     *
     * @return      array
     *
     * @see         IUriContainer::getUriParts()
     */
    public function getUriParts()
    {
        return $this->configUriParts;
    }

    /**
     * Return our original uri.
     *
     * @return      string
     *
     * @see         IUriContainer::getUri()
     */
    public function getUri()
    {
        return $this->configUri;
    }

    // ---------------------------------- </IUriContainer METHODS> -------------------------------
    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Load our settings from our parsed config source
     * and convert them to an array representation.
     *
     * @param       string $configSrc
     *
     * @return      array
     *
     * @throws      ImportConfigException
     *
     * @uses        ResourceBasedConfig::parseUri()
     * @uses        ResourceBasedConfig::loadResource()
     */
    protected function load($configSrc)
    {
        if (!is_string($configSrc))
        {
            throw new ImportConfigException(
                "The given config-uri is expected to be by the type of 'string' but is not."
            );
        }

        $this->configUriParts = $this->parseUri($configSrc);
        $this->configUri = $configSrc;

        return $this->loadResource();
    }

    /**
     * Parse the given uri into an array,
     * that reflects latter's different parts.
     *
     * @param       string $configUri
     *
     * @return      array
     *
     * @throws      ImportConfigException
     */
    protected function parseUri($configUri)
    {
        $uriParts = parse_url($configUri);

        if (!$uriParts)
        {
            throw new ImportConfigException(
                "Unable to parse the given uri: " . $configUri
            );
        }

        return $uriParts;
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>