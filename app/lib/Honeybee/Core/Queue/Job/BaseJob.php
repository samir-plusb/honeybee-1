<?php

namespace Honeybee\Core\Queue\Job;

use Honeybee\Core\Queue\IQueueItem;

abstract class BaseJob implements IJob, IQueueItem
{
    protected $state = self::STATE_FRESH;

    protected $errors = array();

    protected $maxRetries = 3;

    abstract protected function execute();

    public function __construct(array $state = array())
    {
        $this->hydrate($state);
    }

    public function run(array $parameters = array())
    {
        try
        {
            $this->execute($parameters);
            $this->setState(self::STATE_SUCCESS);
        }
        catch(\Exception $e)
        {
            $this->errors[] = $e->getMessage();

            if ($this->getErrorCount() < $this->maxRetries)
            {
                $this->setState(self::STATE_ERROR);
            }
            else
            {
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
        static $validStates = array(
            self::STATE_FRESH, self::STATE_SUCCESS, 
            self::STATE_ERROR, self::STATE_FATAL
        );

        if (! in_array($state, $validStates))
        {
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
        $propBlacklist = $this->getPropertyBlacklist();

        foreach (get_class_vars(get_class($this)) as $propName => $default)
        {
            if (!in_array($propName, $propBlacklist))
            {
                $data[$propName] = (NULL === $this->$propName) ? $default : $this->$propName;
            }
        }

        return $data;
    }

    protected function hydrate(array $data)
    {
        foreach (get_class_vars(get_class($this)) as $propName => $default)
        {
            if (isset($data[$propName]))
            {
                $this->$propName = $data[$propName];
            }
        }
    }

    protected function getPropertyBlacklist()
    {
        return array();
    }
}
