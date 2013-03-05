<?php

namespace Honeybee\Core\Security\Auth;

/**
 * The AuthResponse class is the default implementation of the IAuthResponse interface.
 * It provides data representing the result of an authentication attempt.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 */
class AuthResponse implements IAuthResponse
{
    const STATE_AUTHORIZED = TRUE;

    const STATE_UNAUTHORIZED = FALSE;

    const STATE_ERROR = -1;

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
