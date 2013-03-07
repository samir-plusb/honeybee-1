<?php

namespace Honeybee\Core\Import\Provider;

/**
 * IProvider implementations are responseable for wrapping data access to any desired provider.
 * In order to support streaming large data-sources the interface exposes data to traversal access only,
 * allowing to safely stream whatever data.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
interface IProvider
{
    /**
     * Return the name of our provider.
     *
     * @return      string
     */
    public function getName();

    /**
     * Return the description of this provider.
     *
     * @return      string
     */
    public function getDescription();

    /**
     * Initialize the provider with extrinsic parameters.
     *
     * @param       array $parameters
     */
    public function initialize(array $parameters = array());

    /**
     * Pulls the next set of data from our source
     * and returns it as a corresponding php array.
     *
     * @return      array
     */
    public function provideNextItem();
}
