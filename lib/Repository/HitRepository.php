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
INSERT INTO hits(x, y, success, game_id, user_id)
VALUES(:x, :y, :success, :game_id, :user_id);
SQL;

  private static $COUNT_SUCCESSFUL_HITS_FOR_USER_IN_GAME = <<<'SQL'
SELECT count(*) FROM hits
WHERE success = true
AND user_id = :user_id
AND game_id = :game_id;
SQL;

  private static $FETCH_BY_USER_IN_GAME_QUERY = <<<'SQL'
SELECT * FROM hits
WHERE user_id = :user_id
AND game_id = :game_id;
SQL;

  /**
   * @param Hit $hit
   * @throws RepositoryError
   */
  public function create(Hit $hit)
  {
    try {
      $stmt = $this->dbh->prepare(static::$CREATE_QUERY);
      $stmt->bindValue('x', $hit->getX(), PDO::PARAM_INT);
      $stmt->bindValue('y', $hit->getY(), PDO::PARAM_INT);
      $stmt->bindValue('success', $hit->isSuccess(), PDO::PARAM_BOOL);
      $stmt->bindValue('user_id', $hit->getUserId(), PDO::PARAM_INT);
      $stmt->bindValue('game_id', $hit->getGameId(), PDO::PARAM_INT);
      $stmt->execute();
      $hit->setId($this->dbh->lastInsertId());
    } catch (PDOException $error) {
      throw RepositoryError::wrap($error);
    }
  }

  public function fetchByUserInGame(User $user, Game $game)
  {
    try {
      $stmt = $this->dbh->prepare(static::$FETCH_BY_USER_IN_GAME_QUERY);
      $stmt->bindValue('user_id', $user->getId(), PDO::PARAM_INT);
      $stmt->bindValue('game_id', $game->getId(), PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetchAll();
    } catch (PDOException $error) {
      throw RepositoryError::wrap($error);
    }
  }

  /**
   * @param User $user
   * @param Game $game
   * @return int
   * @throws RepositoryError
   */
  public function countSuccessfulHits(User $user, Game $game)
  {
    try {
      $stmt = $this->dbh->prepare(static::$COUNT_SUCCESSFUL_HITS_FOR_USER_IN_GAME);
      $stmt->bindValue('user_id', $user->getId(), PDO::PARAM_INT);
      $stmt->bindValue('game_id', $game->getId(), PDO::PARAM_INT);
      $stmt->execute();
      return (int) $stmt->fetchColumn();
    } catch (PDOException $error) {
      throw RepositoryError::wrap($error);
    }
  }
}
