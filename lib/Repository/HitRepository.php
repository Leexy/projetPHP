<?php
namespace Repository;

use PDO;
use PDOException;
use Repository\Error\Base as RepositoryError;

use Entity\Hit;
use Entity\Game;
use Entity\User;

class HitRepository extends Base
{
  private static $CREATE_QUERY = <<<'SQL'
INSERT INTO hits(x, y, game_id, user_id)
VALUES(:x, :y, :game_id, :user_id);
SQL;

  public function create(Hit $hit)
  {
    try {
      $stmt = $this->dbh->prepare(static::$CREATE_QUERY);
      $stmt->bindValue('x', $hit->getX(), PDO::PARAM_INT);
      $stmt->bindValue('y', $hit->getY(), PDO::PARAM_INT);
      $stmt->bindValue('user_id', $hit->getUserId(), PDO::PARAM_INT);
      $stmt->bindValue('game_id', $hit->getGameId(), PDO::PARAM_INT);
      $stmt->execute();
      $hit->setId($this->dbh->lastInsertId());
    } catch (PDOException $error) {
      throw RepositoryError::wrap($error);
    }
  }
}
