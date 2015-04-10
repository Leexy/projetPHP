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

  public function create($x, $y, Game $game, User $user)
  {
    try {
      $stmt = $this->dbh->prepare(static::$CREATE_QUERY);
      $stmt->bindValue('x', $x, PDO::PARAM_INT);
      $stmt->bindValue('y', $y, PDO::PARAM_INT);
      $stmt->bindValue('user_id', $user->getId(), PDO::PARAM_INT);
      $stmt->bindValue('game_id', $game->getId(), PDO::PARAM_INT);
      $stmt->execute();
      return new Hit([
        'id' => $this->dbh->lastInsertId(),
        'x' => $x,
        'y' => $y,
        'user_id' => $user->getId(),
        'game_id' => $game->getId(),
      ]);
    } catch (PDOException $error) {
      throw RepositoryError::wrap($error);
    }
  }
}
