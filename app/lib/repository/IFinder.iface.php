<?php

interface IFinder
{
    public function findOne($query);

    public function findMany($query, $limit = 0, $offset = 0);

    public function findAll($limit = 0, $offset = 0);
}
