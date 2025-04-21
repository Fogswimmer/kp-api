<?php
namespace  App\Exception\NotFound;
class AssessmentNotFoundException extends \RuntimeException
{
  public function __construct()
  {
    parent::__construct('Assessment not found');
  }

}