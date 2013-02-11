<?php

class TreeConfig implements ITreeConfig
{
    protected $clientSideController;

    protected $routeName;

    protected $typeKey;

    public static function create(array $data = array())
    {
        return empty($data) ? new static : new static($data);
    }

    public function getClientSideController()
    {
        return $this->clientSideController;
    }

    public function getTypeKey()
    {
        return $this->typeKey;
    }

    public function getRouteName()
    {
        return $this->routeName;
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

