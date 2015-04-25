<?php
namespace Repository;

use PDO;
//classe mere de tous les Repository
//contient le fonctionnement commun a tous les Repository
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
