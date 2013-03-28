<?php

namespace Honeybee\Core\Security\Auth;

interface IPasswordHandler
{
	public function hash($password);

	public function verify($password, $goodHash);
}

