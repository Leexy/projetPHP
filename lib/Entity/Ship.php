<?php

namespace Entity;
//represente un bateau d'un joueur dans une partie
class Ship extends Base
{
  const ORIENTATION_VERTICAL = 'VERTICAL';
  const ORIENTATION_HORIZONTAL = 'HORIZONTAL';

  public function __construct($data = [])
  {
    parent::__construct($data);
    $this->checkOrientationValidity();
  }

  public function getSize()
  {
    return $this->data['size'];
  }

  public function getX()
  {
    return $this->data['x'];
  }

  public function getY()
  {
    return $this->data['y'];
  }

  public function getOwnerId()
  {
    return $this->getUserId();
  }

  public function getUserId()
  {
    return $this->data['user_id'];
  }

  public function getGameId()
  {
    return $this->data['game_id'];
  }

  public function isDestroyed()
  {
    return $this->getWounds() >= $this->getSize();
  }

  public function getWounds()
  {
    return $this->data['wounds'];
  }

  public function setWounds($wounds)
  {
    $this->data['wounds'] = $wounds;
  }

  public function isVertical()
  {
    return $this->getOrientation() === self::ORIENTATION_VERTICAL;
  }

  public function isHorizontal()
  {
    return $this->getOrientation() === self::ORIENTATION_HORIZONTAL;
  }

  public function getOrientation()
  {
    return $this->data['orientation'];
  }
  //defini si le bateau est touche par le tir ou non
  public function isHitBy(Hit $hit)
  {
    $isHit = true;
    if ($this->isHorizontal()) {
      $isHit = (
        $hit->getX() >= $this->getX() and
        $hit->getX() < ($this->getX() + $this->getSize()) and
        $this->getY() == $hit->getY()
      );
    } elseif ($this->isVertical()) {
      $isHit = (
        $hit->getY() >= $this->getY() and
        $hit->getY() < ($this->getY() + $this->getSize()) and
        $this->getX() == $hit->getX()
      );
    }
    return $isHit;
  }

  private function checkOrientationValidity()
  {
    $orientation = $this->getOrientation();
    if (!empty($orientation) && !in_array($orientation, [self::ORIENTATION_HORIZONTAL, self::ORIENTATION_VERTICAL])) {
      throw new \RuntimeException("invalid orientation '$orientation'");
    }
  }
}
