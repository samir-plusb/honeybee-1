<?php

namespace Honeybee\Core\Export;

use Honeybee\Core\Dat0r\Document;

interface IExport
{
    public function getName();

    public function getDescription();

    public function export(Document $document);

    public function revoke(Document $document);

    public function setFilters(Filter\FilterList $filters);
}
