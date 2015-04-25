<?php

namespace Entity;
//represente un tir d'un des deux joueurs dans une partie
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

    public function isSuccess()
    {
        return !empty($this->data['success']);
    }

    public function setSuccess($success)
    {
        $this->data['success'] = (bool) $success;
    }

    public function hasDestroyed()
    {
        return !empty($this->data['destroyed']);
    }

    public function setDestroyed($destroyed)
    {
        $this->data['destroyed'] = (bool) $destroyed;
    }
}
