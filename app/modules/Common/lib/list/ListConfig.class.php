<?php

class ListConfig extends FreezableDataObject implements IListConfig
{
    protected $clientSideController;

    protected $pagingRange;

    protected $translationDomain;

    protected $routeName;

    protected $fields = array();

    protected $itemActions = array();

    protected $batchActions = array();

    protected $defaultLimit;

    protected $typeKey;

    protected $suggestField = '_all';

    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function __construct(array $data = array())
    {
        parent::__construct($data);

        $this->freeze();
    }

    public function getTypeKey()
    {
        return $this->typeKey;
    }

    public function getSuggestField()
    {
        return $this->suggestField;
    }

    public function getDefaultLimit()
    {
        return $this->defaultLimit;
    }

    public function getItemActions()
    {
        return $this->itemActions;
    }

    public function getBatchActions()
    {
        return $this->batchActions;
    }

    public function getClientSideController()
    {
        return $this->clientSideController;
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    public function getTranslationDomain()
    {
        return $this->translationDomain;
    }

    public function getPagingRange()
    {
        return $this->pagingRange;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function hasField($fieldname)
    {
        return isset($this->fields[$fieldname]);
    }

    public function getField($fieldname)
    {
        return $this->hasField($fieldname) ? $this->fields[$fieldname] : NULL;
    }

    // Hydrate Setters

    protected function setFields(array $fields)
    {
        $this->breakWhenFrozen();

        foreach ($fields as $fieldname => $fieldParams)
        {
            $this->fields[$fieldname] = ListField::fromArray(array_merge(
                array('name' => $fieldname),
                $fieldParams
            ));
        }
    }

    protected function setPagingRange($pagingRange)
    {
        $this->pagingRange = (int)$pagingRange;
    }

    protected function setDefaultLimit($limit)
    {
        $this->defaultLimit = (int)$limit;
    }
}

?>