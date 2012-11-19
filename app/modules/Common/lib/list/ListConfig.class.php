<?php

class ListConfig implements IListConfig
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

    public static function create(array $data = array())
    {
        return empty($data) ? new static : new static($data);
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
        foreach ($fields as $fieldname => $fieldParams)
        {
            $this->fields[$fieldname] = ListField::create(array_merge(
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

    protected function __construct(array $data = array())
    {
        if (! empty($data))
        {
            $this->hydrate($data);
        }
    }

    protected function hydrate(array $data)
    {
        foreach (array_keys(get_class_vars(get_class($this))) as $prop)
        {

            if (array_key_exists($prop, $data))
            {
                $setter = 'set'.ucfirst($prop);
                if (is_callable(array($this, $setter)))
                {
                    $this->$setter($data[$prop]);
                }
                else
                {
                    $this->$prop = $data[$prop];
                }
            }
        }
    }
}
