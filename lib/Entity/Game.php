<?php

namespace Entity;

class Game extends Base
{
  const STATE_WAITING = 'waiting';
  const STATE_PLAYING = 'playing';

  public function isPlaying(User $user)
  {
    return in_array($user->getId(), [$this->getUser1Id(), $this->getUser2Id()], true);
  }

  public function isPlayerTurn(User $user)
  {
    return $user->getId() === $this->getPlayingUserId();
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

  public function getPlayingUserId()
  {
    return $this->data['playing_user_id'];
  }
}
