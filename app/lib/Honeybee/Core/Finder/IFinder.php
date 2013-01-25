<?php

namespace Honeybee\Core\Finder;

interface IFinder
{
    public function find($query, $limit = 0, $offset = 0);

    public function fetchAll($limit = 0, $offset = 0);
}
