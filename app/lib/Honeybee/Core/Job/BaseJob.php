<?php

namespace Honeybee\Core\Job;

use Honeybee\Core\Job\Queue\IQueueItem;

abstract class BaseJob implements IJob, IQueueItem
{
    protected $state = self::STATE_FRESH;

    protected $errors = array();

    protected $max_retries = 3;

    abstract protected function execute();

    public function __construct(array $state = array())
    {
        $this->hydrate($state);
    }

    public function run(array $parameters = array())
    {
        try {
            $this->execute($parameters);
            $this->setState(self::STATE_SUCCESS);
        }
        catch(\Exception $e) {
            $this->errors[] = $e->getMessage();

            if ($this->getErrorCount() < $this->max_retries) {
                $this->setState(self::STATE_ERROR);
            } else {
                $this->setState(self::STATE_FATAL);
            }
        }

        return $this->state;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        static $valid_states = array(
            self::STATE_FRESH,
            self::STATE_SUCCESS,
            self::STATE_ERROR,
            self::STATE_FATAL
        );

        if (!in_array($state, $valid_states)) {
            throw new Exception("Invalid state given.");
        }

        $this->state = $state;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getErrorCount()
    {
        return count($this->errors);
    }

    public function toArray()
    {
        $data = array();
        $property_blacklist = $this->getPropertyBlacklist();

        foreach (get_class_vars(get_class($this)) as $property_name => $default) {
            if (!in_array($property_name, $property_blacklist)) {
                $data[$property_name] = (NULL === $this->$property_name) ? $default : $this->$property_name;
            }
        }

        return $data;
    }

    protected function hydrate(array $data)
    {
        foreach (get_class_vars(get_class($this)) as $property_name => $default)
        {
            if (isset($data[$property_name]))
            {
                $this->$property_name = $data[$property_name];
            }
        }
    }

    protected function getPropertyBlacklist()
    {
        return array();
    }
}
