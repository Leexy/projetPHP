<?php
namespace Repository;

use PDO;
use PDOException;
use Repository\Error\Base as RepositoryError;

use Entity\Ship;
use Entity\User;
use Entity\Game;

class ShipRepository extends Base
{
  private static $FETCH_FOR_USER_IN_GAME_QUERY = <<<'SQL'
SELECT * FROM ships
WHERE game_id = :game_id
AND user_id = :user_id;
SQL;

  public function fetchForUserInGame(User $user, Game $game)
  {
    try {
      $stmt = $this->dbh->prepare(static::$FETCH_FOR_USER_IN_GAME_QUERY);
      $stmt->bindValue('user_id', $user->getId(), PDO::PARAM_INT);
      $stmt->bindValue('game_id', $game->getId(), PDO::PARAM_INT);
      $stmt->execute();
      $ships = [];
      foreach ($stmt->fetchAll() as $shipData) {
        $ships[] = new Ship($shipData);
      }
      return $ships;
    } catch (PDOException $error) {
      throw RepositoryError::wrap($error);
    }
  }
}
