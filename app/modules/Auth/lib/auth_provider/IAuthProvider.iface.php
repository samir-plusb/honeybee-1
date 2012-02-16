<?php

interface IAuthProvider
{
    public function getTypeIdentifier();

    public function authenticate($username, $password, $options = array());
}

?>
