<?php

namespace Entity;

class Base
{
  /**
   * @var array
   */
  protected $data;

  /**
   * @param array $data
   */
  public function __construct($data = [])
  {
    if (!is_array($data)) {
        $data = ['id' => $data];
    }
    $this->data = $data;
  }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->data['id'];
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->data['id'] = $id;
    }

  /**
   * @return bool
   */
  public function isPersisted()
  {
    return !empty($this->data['id']);
  }
}
