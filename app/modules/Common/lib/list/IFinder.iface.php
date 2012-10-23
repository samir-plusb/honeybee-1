<?php

interface IFinder
{
    const DEFAULT_LIMIT = 50;

    const SORT_DESC = 'desc';

    const SORT_ASC = 'asc';
    
	public static function create(IListConfig $listConfig);

	public function find(IListState $listState);
}

?>