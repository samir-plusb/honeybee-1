<?php

namespace Honeybee\Core\Import\Consumer;

/**
 * The IConsumerReport interface defines how data-import results are returned from the IConsumer's
 * run method.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 */
interface IConsumerReport
{
    public function addRecordSuccess(array $item, $msg = '');

    public function addRecordError(array $item, $msg = '');

    public function getSuccessCount();

    public function getErrors();

    public function hasErrors();
}
