<?php
namespace Repository\Error;

class Base extends \Exception
{
  public static function wrap(\Exception $exception)
  {
    /*
    var_dump($exception->getMessage());
    echo '<pre>';
    echo $exception->getTraceAsString();
    exit;
    */
    return new static($exception->getMessage(), $exception->getCode(), $exception);
  }
}
