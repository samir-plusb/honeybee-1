<?php

namespace Honeybee\Core\Import\Consumer;

use Honeybee\Core\Import\Config;
use Honeybee\Core\Import\Provider;
use Honeybee\Core\Import\Filter;

/**
 * IConsumer implementations are responseable for importing IDataRecords
 * to any required location.
 * They shall receive latter IDataRecords from a given IProvider implementation.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
interface IConsumer
{
    /**
     * Return the name of our consumer.
     *
     * @return      string
     */
    public function getName();

    /**
     * Return the description of this consumer.
     *
     * @return      string
     */
    public function getDescription();

    /**
     * Initialize the consumer with extrinsic parameters.
     *
     * @param       array $parameters
     */
    public function initialize(array $parameters = array());

    /**
     * Set the filters to pass the data through during import.
     *
     * @param       FilterList $filters
     */
    public function setFilters(Filter\FilterList $filters);

    /**
     * Imort all IDataRecords provided by the given IProvider.
     *
     * @param       IProvider $dataSource
     */
    public function consume(Provider\IProvider $dataSource);
}
