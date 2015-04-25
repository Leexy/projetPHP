<?php

namespace Entity;

class Game extends Base
{
  const STATE_WAITING = 'waiting';
  const STATE_PLACING = 'placing';
  const STATE_PLAYER1_READY = 'player1_ready';
  const STATE_PLAYER2_READY = 'player2_ready';
  const STATE_PLAYING = 'playing';
  const STATE_FINISHED = 'finished';

  public static function getStartedStates()
  {
    return [static::STATE_PLACING, static::STATE_PLAYING];
  }

  public function isPlaying(User $user)
  {
    return in_array($user->getId(), [$this->getUser1Id(), $this->getUser2Id()], true);
  }

  public function isPlayerTurn(User $user)
  {
    return $user->getId() === $this->getPlayingUserId();
  }

  public function isPlayerReady(User $user)
  {
    $currentState = $this->getState();
    if (in_array($currentState, [Game::STATE_WAITING, Game::STATE_PLACING])) {
      return false;
    }
    if (in_array($currentState, [Game::STATE_PLAYING, Game::STATE_FINISHED])) {
      return true;
    }
    if (in_array($currentState, [Game::STATE_PLAYER1_READY, Game::STATE_PLAYER2_READY])) {
      if ($user->getId() === $this->getUser1Id()) {
        return $currentState === Game::STATE_PLAYER1_READY;
      } else if ($user->getId() === $this->getUser2Id()) {
        return $currentState === Game::STATE_PLAYER2_READY;
      } else {
        throw new \RuntimeException(sprintf('Error: user[%s] not in game[%s].', $user->getId(), $this->getId()));
      }
    } else {
      throw new \RuntimeException("Error: unhandled state '$currentState'.");
    }
  }

  public function setState($state)
  {
    $this->data['state'] = $state;
  }

  public function getState()
  {
    return $this->data['state'];
  }

  public function getUser1Id()
  {
    return $this->data['user1_id'];
  }

  public function getUser2Id()
  {
    return $this->data['user2_id'];
  }

  public function getWinnerId()
  {
    return $this->data['winner_id'];
  }

  public function setWinnerId($winnerId)
  {
    $this->data['winner_id'] = $winnerId;
  }

  public function getOpponentIdOf(User $player)
  {
    return $player->getId() === $this->getUser1Id() ?
        $this->getUser2Id() :
        $this->getUser1Id();
  }

  public function getPlayingUserId()
  {
    return $this->data['playing_user_id'];
  }
}
