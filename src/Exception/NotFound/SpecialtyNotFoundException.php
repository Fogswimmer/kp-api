<?php

namespace App\Exception\NotFound;

class SpecialtyNotFoundException extends \RuntimeException
{
	public function __construct()
	{
		parent::__construct('The person does not have this specialty');
	}
}