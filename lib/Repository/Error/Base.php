<?php
namespace Repository\Error;

class Base extends \Exception
{
  public static function wrap(\Exception $exception)
  {
    return new static($exception->getMessage(), $exception->getCode(), $exception);
  }
}
