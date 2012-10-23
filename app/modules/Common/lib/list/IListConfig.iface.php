<?php

interface IListConfig extends IDataObject
{
    // returns the typeKey (news, shofi ...) for the module from which's context the config has been created for.
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
}

?>
