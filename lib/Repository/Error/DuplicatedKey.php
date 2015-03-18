<?php
namespace Repository\Error;

use Exception;

class DuplicatedKey extends Base
{
  /**
   * @var string
   */
  private $key;

  /**
   * @var mixed
   */
  private $value;

  /**
   * @param string $key
   * @param mixed $value
   * @param Exception $previous
   */
  public function __construct($key, $value = null, Exception $previous = null)
  {
    $this->key = $key;
    $this->value = $value;
    $message = $value !== null ?
      "the value '$value'' for the key '$key' already exists'" :
      "duplicated key '$key'";
    parent::__construct($message, $previous->getCode(), $previous);
  }

  /**
   * @return string
   */
  public function getKey()
  {
    return $this->key;
  }

  /**
   * @return mixed
   */
  public function getValue()
  {
    return $this->value;
  }
}
