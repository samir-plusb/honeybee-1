<?php

interface IListField
{
    public function getName();

    public function getValueField();

    public function setValueField($valuefield);

    public function hasValueField();

    public function getSortField();

    public function setSortField($sortfield);

    public function hasSortField();

    public function getRenderer();

    public function setRenderer($renderer);

    public function hasRenderer();
}
