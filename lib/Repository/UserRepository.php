<?php
namespace Repository;

use PDO;
use PDOException;
use Repository\Error\Base as RepositoryError;
use Repository\Error\DuplicatedKey;

use Entity\User;

class UserRepository extends Base
{
  private static $DUPLICATION_CODE = '23000';

  private static $CREATION_QUERY = <<<'SQL'
INSERT INTO users(email, password)
VALUES(:email, :password);
SQL;

  private static $FETCH_BY_ID_QUERY = <<<'SQL'
SELECT * FROM users WHERE id = :user_id;
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

  public function fetchById($userId)
  {
    try {
      $stmt = $this->dbh->prepare(static::$FETCH_BY_ID_QUERY);
      $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
      $stmt->execute();
      return new User($stmt->fetch());
    } catch (PDOException $error) {
      RepositoryError::wrap($error);
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
