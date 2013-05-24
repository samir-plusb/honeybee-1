<?php

interface IListConfig
{
    public function getTypeKey();

    public function getSuggestField();

	public function getDefaultLimit();

	public function getItemActions();

    public function getBatchActions();

	public function getClientSideController();

	public function getRouteName();

    public function getTranslationDomain();

    public function getPagingRange();

    public function getFields();

    public function getQueryBuilder();
}
