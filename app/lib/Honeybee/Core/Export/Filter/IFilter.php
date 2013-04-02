<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\Document;

/**
 * IFilter implementations are responseable for wrapping data manipulation during export such as 
 * converting references to aggregated arrays, structuring image meta data etc.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
interface IFilter
{
    /**
     * Return the name of our filter.
     *
     * @return      string
     */
    public function getName();

    /**
     * Process the given input and return a corresponding deterministic output.
     *
     * @param       array $input
     *
     * @return      array
     */
    public function execute(Document $document);
}
