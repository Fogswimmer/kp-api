<?php

namespace App\Exception\NotFound;

class PersonNotFoundException extends \RuntimeException
{
	public function __construct()
	{
		parent::__construct('Person not found');
	}
}