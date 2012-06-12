<?php


interface IQueryBuilder
{
    public function build(IListState $listState, $filterDeleted = TRUE);
}

?>
