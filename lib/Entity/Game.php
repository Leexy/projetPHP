<?php

namespace Entity;

class Game extends Base
{
  const STATE_WAITING = 'waiting';
  const STATE_PLAYING = 'playing';

  public function getUser1Id()
  {
  	return $this->data['user1_id'];
  }

  public function getUser2Id()
  {
  	return $this->data['user2_id'];
  }
}
