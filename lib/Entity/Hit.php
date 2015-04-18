<?php

namespace Entity;

class Hit extends Base
{
    public function getX()
    {
        return $this->data['x'];
    }

    public function getY()
    {
        return $this->data['y'];
    }

    public function isSuccess()
    {
        return !empty($this->data['success']);
    }

    public function getUserId()
    {
        return $this->data['user_id'];
    }

    public function getGameId()
    {
        return $this->data['game_id'];
    }

    public function getPosition()
    {
        return ['x' => $this->getX(), 'y' => $this->getY()];
    }

    public function setUserId($userId)
    {
        $this->data['user_id'] = $userId;
    }

    public function setGameId($gameId)
    {
        $this->data['game_id'] = $gameId;
    }

    public function setSuccess($success)
    {
        $this->data['success'] = (bool) $success;
    }
}
