<?php
namespace  App\Exception\NotFound;
class FileNotFoundException extends \RuntimeException
{
  public function __construct()
  {
    parent::__construct('File not found');
  }

}