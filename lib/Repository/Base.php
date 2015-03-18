<?php
namespace Repository;

use PDO;

class Base
{
  /**
   * @var PDO
   */
  protected $dbh;

  /**
   * @param PDO $dbh
   */
  public function __construct(PDO $dbh = null)
  {
    $this->dbh = $dbh;
  }

  /**
   * @param PDO $dbh
   */
  public function setDbh($dbh)
  {
    $this->dbh = $dbh;
  }
}
