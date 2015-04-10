<?php
namespace Repository;

use PDO;
use PDOException;
use Repository\Error\Base as RepositoryError;

use Entity\User;

use Entity\Game;
use Repository\Error\GameCreationLimit;
use Repository\Error\FullGame;

class GameRepository extends Base
{
  private static $CREATE_QUERY = <<<'SQL'
INSERT INTO games(user1_id, state)
VALUES(:user1_id, :state);
SQL;

  private static $TRY_TO_ADD_USER_QUERY = <<<'SQL'
UPDATE games SET user2_id = :user2_id, state = :state, playing_user_id = user1_id
WHERE id = :game_id
AND user2_id IS NULL;
SQL;

  private static $COUNT_BY_STATE_FOR_USER_QUERY = <<<'SQL'
SELECT count(id) FROM games
WHERE user1_id = :user1_id
AND state = :state;
SQL;

  private static $FETCH_BY_STATE_QUERY = <<<'SQL'
SELECT * FROM games
WHERE state = :state;
SQL;

  private static $FETCH_BY_ID_QUERY = <<<'SQL'
SELECT * FROM games
WHERE id = :id;
SQL;

  private static $SWITCH_PLAYING_USER_QUERY = <<<'SQL'
UPDATE games
SET playing_user_id = IF(playing_user_id = user1_id, user2_id, user1_id)
WHERE id = :game_id;
SQL;

  public function tryToAddUser(User $user, Game $game)
  {
    try {
      $stmt = $this->dbh->prepare(static::$TRY_TO_ADD_USER_QUERY);
      $stmt->bindValue('game_id', $game->getId(), PDO::PARAM_INT);
      $stmt->bindValue('user2_id', $user->getId(), PDO::PARAM_INT);
      $stmt->bindValue('state', Game::STATE_PLAYING, PDO::PARAM_STR);
      $affectedRows = $stmt->execute();
      if (!$affectedRows) {
        throw new FullGame();
      }
    } catch (PDOException $error) {
      RepositoryError::wrap($error);
    }
  }

  public function switchPlayingUser(Game $game)
  {
    try {
      $stmt = $this->dbh->prepare(static::$SWITCH_PLAYING_USER_QUERY);
      $stmt->bindValue('game_id', $game->getId(), PDO::PARAM_INT);
      $stmt->execute();
    } catch (PDOException $error) {
      RepositoryError::wrap($error);
    }
  }

  public function fetchById($gameId)
  {
    try {
      $stmt = $this->dbh->prepare(static::$FETCH_BY_ID_QUERY);
      $stmt->bindValue('id', $gameId, PDO::PARAM_INT);
      $stmt->execute();
      return new Game($stmt->fetch());
    } catch (PDOException $error) {
      RepositoryError::wrap($error);
    }
  }

  public function createFor(User $user)
  {
    try {
      $this->dbh->beginTransaction();
      $stmt = $this->dbh->prepare(static::$COUNT_BY_STATE_FOR_USER_QUERY);
      $stmt->bindValue('user1_id', $user->getId(), PDO::PARAM_INT);
      $stmt->bindValue('state', Game::STATE_WAITING, PDO::PARAM_STR);
      $stmt->execute();
      $count = (int) $stmt->fetchColumn();
      if ($count) {
        throw new GameCreationLimit();
      }

      $stmt = $this->dbh->prepare(static::$CREATE_QUERY);
      $stmt->bindValue('user1_id', $user->getId(), PDO::PARAM_INT);
      $stmt->bindValue('state', Game::STATE_WAITING, PDO::PARAM_STR);
      $stmt->execute();
      $lastInsertId = $this->dbh->lastInsertId();
      $this->dbh->commit();
      return $lastInsertId;
    } catch (GameCreationLimit $error) {
      $this->dbh->rollBack();
      throw $error;
    } catch (PDOException $error) {
      $this->dbh->rollBack();
      throw RepositoryError::wrap($error);
    }
  }

  public function fetchWaiting()
  {
    try {
      $stmt = $this->dbh->prepare(static::$FETCH_BY_STATE_QUERY);
      $stmt->bindValue('state', Game::STATE_WAITING, PDO::PARAM_STR);
      $stmt->execute();
      return $stmt->fetchAll();
    } catch(PDOException $error) {
      throw RepositoryError::wrap($error);
    }
  }
}
