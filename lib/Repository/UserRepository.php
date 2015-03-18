<?php
namespace Repository;

use PDO;
use PDOException;
use Repository\Error\Base as RepositoryError;
use Repository\Error\DuplicatedKey;

class UserRepository extends Base
{
  private static $DUPLICATION_CODE = '23000';

  private static $CREATION_QUERY = <<<'SQL'
INSERT INTO users(email, password)
VALUES(:email, :password);
SQL;

  public function create($email, $password)
  {
    try {
      $stmt = $this->dbh->prepare(static::$CREATION_QUERY);
      $stmt->bindValue('email', $email, PDO::PARAM_STR);
      $stmt->bindValue('password', $this->hash($password), PDO::PARAM_STR);
      $stmt->execute();
    } catch (PDOException $error) {
      if ($error->getCode() === static::$DUPLICATION_CODE) {
        throw new DuplicatedKey('email', $email, $error);
      } else {
        throw RepositoryError::wrap($error);
      }
    }
  }

  /**
   * @param $password
   * @return string
   */
  public function hash($password)
  {
    return password_hash($password, PASSWORD_DEFAULT);
  }
}
