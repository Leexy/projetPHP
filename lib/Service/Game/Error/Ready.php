<?php
namespace Service\Game\Error;


use Entity\User;

class Ready extends GameError
{
  const CODE_INVALID_GAME_STATE = 100;
  const CODE_FLEET_NOT_READY = 101;

  public static function invalidGameState($state)
  {
    return new self("cannot update state to ready: state[$state]", static::CODE_INVALID_GAME_STATE);
  }

  public static function fleetNotReady(User $player, $shipsCount)
  {
    return new self("user[{$player->getId()}]'s fleet is not ready (only '$shipsCount' ships)", static::CODE_FLEET_NOT_READY);
  }
}
