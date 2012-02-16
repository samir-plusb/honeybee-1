<?php

interface IAuthResponse
{
    public function getMessage();

    public function getErrors();

    public function getState();

    public function getAttributes();

    public function getAttribute($name, $default = NULL);
}

?>
