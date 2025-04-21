<?php

namespace App\Exception\NotFound;

class FilmNotFoundException extends \RuntimeException
{
	public function __construct()
	{
		parent::__construct('Film not found');
	}
}