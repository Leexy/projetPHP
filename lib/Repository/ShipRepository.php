<?php
namespace Repository;

use PDO;
use PDOException;
use Repository\Error\Base as RepositoryError;

use Entity\Ship;
use Entity\User;
use Entity\Game;
//contient les operations de persistence relatives a l'entite Ship
class ShipRepository extends Base
{
  private static $CREATE_SHIP = <<<'SQL'
INSERT INTO ships(`size`, x, y, orientation, user_id, game_id)
VALUES(:size, :x, :y, :orientation, :user_id, :game_id);
SQL;

  private static $UPDATE_SHIP = <<<'SQL'
UPDATE ships SET x = :x, y = :y, orientation = :orientation
WHERE id = :ship_id
SQL;

  private static $FETCH_FOR_USER_IN_GAME_QUERY = <<<'SQL'
SELECT * FROM ships
WHERE game_id = :game_id
AND user_id = :user_id;
SQL;

  private static $FETCH_SUNK_FOR_USER_IN_GAME_QUERY = <<<'SQL'
SELECT * FROM ships
WHERE wounds >= size
AND game_id = :game_id
AND user_id = :user_id;
SQL;

  private static $WOUND = <<<'SQL'
UPDATE ships SET wounds = wounds + 1
WHERE id = :ship_id;
SQL;

  public function create(Ship $ship, Game $game, User $owner)
  {
    try {
      $stmt = $this->dbh->prepare(static::$CREATE_SHIP);
      $stmt->bindValue('size', $ship->getSize(), PDO::PARAM_INT);
      $stmt->bindValue('x', $ship->getX(), PDO::PARAM_INT);
      $stmt->bindValue('y', $ship->getY(), PDO::PARAM_INT);
      $stmt->bindValue('orientation', $ship->getOrientation(), PDO::PARAM_STR);
      $stmt->bindValue('user_id', $owner->getId(), PDO::PARAM_INT);
      $stmt->bindValue('game_id', $game->getId(), PDO::PARAM_INT);
      $stmt->execute();
      $ship->setId($this->dbh->lastInsertId());
      return $ship;
    } catch (PDOException $error) {
      throw RepositoryError::wrap($error);
    }
  }

  public function update(Ship $ship)
  {
    try {
      $stmt = $this->dbh->prepare(static::$UPDATE_SHIP);
      $stmt->bindValue('ship_id', $ship->getId(), PDO::PARAM_INT);
      $stmt->bindValue('x', $ship->getX(), PDO::PARAM_INT);
      $stmt->bindValue('y', $ship->getY(), PDO::PARAM_INT);
      $stmt->bindValue('orientation', $ship->getOrientation(), PDO::PARAM_STR);
      $stmt->execute();
      return $ship;
    } catch (PDOException $error) {
      throw RepositoryError::wrap($error);
    }
  }

  public function wound(Ship $ship)
  {
    try {
      $stmt = $this->dbh->prepare(static::$WOUND);
      $stmt->bindValue('ship_id', (int)$ship->getId(), PDO::PARAM_INT);
      $stmt->execute();
      $ship->setWounds($ship->getWounds() + 1);
    } catch (PDOException $error) {
      throw RepositoryError::wrap($error);
    }
  }

  /**
   * @param User $user
   * @param Game $game
   * @return Ship[]
   * @throws RepositoryError
   */
  public function fetchForUserInGame(User $user, Game $game, $raw = false)
  {
    try {
      $stmt = $this->dbh->prepare(static::$FETCH_FOR_USER_IN_GAME_QUERY);
      $stmt->bindValue('user_id', $user->getId(), PDO::PARAM_INT);
      $stmt->bindValue('game_id', $game->getId(), PDO::PARAM_INT);
      $stmt->execute();
      if ($raw) {
        return $stmt->fetchAll();
      }
      $ships = [];
      foreach ($stmt->fetchAll() as $shipData) {
        $ships[] = new Ship($shipData);
      }
      return $ships;
    } catch (PDOException $error) {
      throw RepositoryError::wrap($error);
    }
  }

  /**
   * @param User $user
   * @param Game $game
   * @return Ship[]
   * @throws RepositoryError
   */
  public function fetchSunkForUserInGame(User $user, Game $game, $raw = false)
  {
    try {
      $stmt = $this->dbh->prepare(static::$FETCH_SUNK_FOR_USER_IN_GAME_QUERY);
      $stmt->bindValue('user_id', $user->getId(), PDO::PARAM_INT);
      $stmt->bindValue('game_id', $game->getId(), PDO::PARAM_INT);
      $stmt->execute();
      if ($raw) {
        return $stmt->fetchAll();
      }
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
