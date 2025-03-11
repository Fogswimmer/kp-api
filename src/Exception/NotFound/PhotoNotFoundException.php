<?php

namespace App\Exception\NotFound;

class PhotoNotFoundException extends \RuntimeException
{
	public function __construct()
	{
		parent::__construct('Photo not found');
	}
}