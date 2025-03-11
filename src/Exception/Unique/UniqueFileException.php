<?php
namespace  App\Exception\Unique;

class UniqueFileException extends \Exception {
  public function __construct() {
    parent::__construct('File already exists');
  }
}