<?php

class FinderResult extends BaseDataObject
{
	protected $totalCount = 0;

	protected $items = array();

	public static function fromArray(array $data = array())
	{
		return new self($data);
	}

	public function getItemsCount()
	{
		return count($this->items);
	}

	public function getTotalCount()
	{
		return $this->totalCount;
	}

	public function getItems()
	{
		return $this->items;
	}
}

?>