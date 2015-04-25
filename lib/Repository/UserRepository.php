<?php
namespace Repository;

use PDO;
use PDOException;
use Repository\Error\Base as RepositoryError;
use Repository\Error\DuplicatedKey;

use Entity\User;
//contient les operations de persistence relatives a l'entite User
class UserRepository extends Base
{
  private static $DUPLICATION_CODE = '23000';

  private static $CREATION_QUERY = <<<'SQL'
INSERT INTO users(display_name, email, password)
VALUES(:display_name, :email, :password);
SQL;

  private static $FETCH_BY_ID_QUERY = <<<'SQL'
SELECT * FROM users WHERE id = :user_id;
SQL;

  private static $FETCH_TOP_TEN = <<<'SQL'
SELECT users.id, display_name, COUNT(games.winner_id) as victories
FROM users
JOIN games ON users.id = games.winner_id
GROUP BY users.id, display_name
ORDER BY victories DESC
LIMIT 10;
SQL;

  public function create($displayName, $email, $password)
  {
    try {
      $stmt = $this->dbh->prepare(static::$CREATION_QUERY);
      $stmt->bindValue('display_name', $displayName, PDO::PARAM_STR);
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
      throw RepositoryError::wrap($error);
    }
  }

  public function fetchTopTen()
  {
    try {
      $stmt = $this->dbh->prepare(static::$FETCH_TOP_TEN);
      $stmt->execute();
      return $stmt->fetchAll();
    } catch (PDOException $error) {
      throw RepositoryError::wrap($error);
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
