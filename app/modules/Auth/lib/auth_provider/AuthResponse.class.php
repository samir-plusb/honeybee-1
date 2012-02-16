<?php

class AuthResponse implements IAuthResponse
{
    const STATE_AUTHORIZED = "authorized";

    const STATE_UNAUTHORIZED = "unauthorized";

    const STATE_ERROR = "error";

    protected $message;

    protected $errors;

    protected $state;

    protected $attributes;

    public function __construct($state, $message, $attributes = array(), $errors = array())
    {
        $this->state = $state;
        $this->errors = $errors;
        $this->message = $message;
        $this->attributes = $attributes;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = NULL)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }
}

?>
