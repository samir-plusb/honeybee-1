<?php

namespace Honeybee\Core\Finder;

interface IQueryBuilder
{
    public function build(array $specification);
}
