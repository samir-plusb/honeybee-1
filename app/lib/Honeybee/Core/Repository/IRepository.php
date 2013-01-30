<?php

namespace Honeybee\Core\Repository;

interface IRepository
{
    public function getFinder();

    public function getStorage();

    public function find($query = NULL, $limit = 0, $offset = 0);

    public function read($identifier);

    public function write($data);
}
