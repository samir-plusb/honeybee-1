<?php

/**
 * The IDataImportReport interface defines how data-import results are returned from the IDataImport's
 * run method.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Import
 * @subpackage DataImport
 */
interface IDataImportReport
{
    public function addRecordSuccess(IDataRecord $record, $msg = '');

    public function addRecordError(IDataRecord $record, $msg = '');

    public function getSuccessCount();

    public function getErrors();

    public function hasErrors();
}

?>