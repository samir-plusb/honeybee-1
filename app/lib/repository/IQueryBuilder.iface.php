<?php

interface IQueryBuilder
{
    public function build(IListConfig $listConfig, IListState $listState);
}
