<?php
namespace Repository;

use PDO;
use PDOException;
use Repository\Error\Base as RepositoryError;

use Entity\User;
use Entity\Hit;

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

  private static $FETCH_FOR_USER_INCLUDING_STATES = <<<'SQL'
SELECT * FROM games
WHERE state IN(%s)
AND (user1_id = :user_id OR user2_id = :user_id);
SQL;

  private static $FETCH_BY_ID_QUERY = <<<'SQL'
SELECT * FROM games
WHERE id = :id;
SQL;

  private static $FETCH_VICTORY_COUNT_FOR_USER = <<<'SQL'
SELECT count(id) FROM games
WHERE winner_id = :user_id;
SQL;

  private static $FETCH_PLAYED_COUNT_FOR_USER = <<<'SQL'
SELECT count(id) FROM games
WHERE state = :finished_state
AND (user1_id = :user_id OR user2_id = :user_id);
SQL;

  private static $SWITCH_PLAYING_USER_QUERY = <<<'SQL'
UPDATE games
SET playing_user_id = IF(playing_user_id = user1_id, user2_id, user1_id), last_hit_id = :hit_id
WHERE id = :game_id;
SQL;

private static $UPGRADE_STATE_TO_READY = <<<'SQL'
UPDATE games
SET state = IF(state = :placing_state, :player_ready_state, :playing_state)
WHERE id = :game_id;
SQL;

  private static $SET_WINNER = <<<'SQL'
UPDATE games SET state = :finished_state, winner_id = :winner_id
WHERE id = :game_id;
SQL;

  public function tryToAddUser(User $user, Game $game)
  {
    try {
      $stmt = $this->dbh->prepare(static::$TRY_TO_ADD_USER_QUERY);
      $stmt->bindValue('game_id', $game->getId(), PDO::PARAM_INT);
      $stmt->bindValue('user2_id', $user->getId(), PDO::PARAM_INT);
      $stmt->bindValue('state', Game::STATE_PLACING, PDO::PARAM_STR);
      $affectedRows = $stmt->execute();
      if (!$affectedRows) {
        throw new FullGame();
      }
    } catch (PDOException $error) {
      RepositoryError::wrap($error);
    }
  }

  public function switchPlayingUser(Game $game, Hit $hit)
  {
    try {
      $stmt = $this->dbh->prepare(static::$SWITCH_PLAYING_USER_QUERY);
      $stmt->bindValue('game_id', $game->getId(), PDO::PARAM_INT);
      $stmt->bindValue('hit_id', $hit->getId(), PDO::PARAM_INT);
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

  public function win(Game $game, User $winner)
  {
    $game->setWinnerId($winner->getId());
    $game->setState(Game::STATE_FINISHED);
    try {
      $stmt = $this->dbh->prepare(static::$SET_WINNER);
      $stmt->bindValue('finished_state', Game::STATE_FINISHED, PDO::PARAM_STR);
      $stmt->bindValue('winner_id', $winner->getId(), PDO::PARAM_INT);
      $stmt->bindValue('game_id', $game->getId(), PDO::PARAM_INT);
      return $stmt->execute();
    } catch(PDOException $error) {
      throw RepositoryError::wrap($error);
    }
  }

  public function fetchWaitingFor(User $user)
  {
    try {
      $stmt = $this->dbh->prepare(sprintf(static::$FETCH_FOR_USER_INCLUDING_STATES, $this->dbh->quote(Game::STATE_WAITING)));
      $stmt->bindValue('user_id', $user->getId(), PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetchAll();
    } catch(PDOException $error) {
      throw RepositoryError::wrap($error);
    }
  }

  public function upgradeStateToReady(Game $game, $readyPlayerIsPlayer1)
  {
    try {
      $playerReadyState = $readyPlayerIsPlayer1 ? Game::STATE_PLAYER1_READY  : Game::STATE_PLAYER2_READY;
      $stmt = $this->dbh->prepare(static::$UPGRADE_STATE_TO_READY);
      $stmt->bindValue('placing_state', Game::STATE_PLACING, PDO::PARAM_STR);
      $stmt->bindValue('playing_state', Game::STATE_PLAYING, PDO::PARAM_STR);
      $stmt->bindValue('player_ready_state', $playerReadyState, PDO::PARAM_STR);
      $stmt->bindValue('game_id', $game->getId(), PDO::PARAM_INT);
      $stmt->execute();
    } catch(PDOException $error) {
      throw RepositoryError::wrap($error);
    }
  }

  public function getVictoryCountFor(User $user)
  {
    try {
      $stmt = $this->dbh->prepare(static::$FETCH_VICTORY_COUNT_FOR_USER);
      $stmt->bindValue('user_id', $user->getId(), PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetchColumn();
    } catch(PDOException $error) {
      throw RepositoryError::wrap($error);
    }
  }

  public function getPlayedCountFor(User $user)
  {
    try {
      $stmt = $this->dbh->prepare(static::$FETCH_PLAYED_COUNT_FOR_USER);
      $stmt->bindValue('finished_state', Game::STATE_FINISHED, PDO::PARAM_STR);
      $stmt->bindValue('user_id', $user->getId(), PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetchColumn();
    } catch(PDOException $error) {
      throw RepositoryError::wrap($error);
    }
  }

  /**
   * @param User $user
   * @return Game[]
   * @throws RepositoryError
   */
  public function fetchStartedFor(User $user)
  {
    $states = implode(', ', array_map([$this->dbh, 'quote'], Game::getStartedStates()));
    try {
      $stmt = $this->dbh->prepare(sprintf(static::$FETCH_FOR_USER_INCLUDING_STATES, $states));
      $stmt->bindValue('user_id', $user->getId(), PDO::PARAM_INT);
      $stmt->execute();
      $result = [];
      foreach ($stmt->fetchAll() as $row) {
        $result[] = new Game($row);
      }
      return $result;
    } catch(PDOException $error) {
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
