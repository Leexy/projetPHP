<?php
namespace Service\Game\Error;


use Entity\Ship;
use Entity\User;

class ShipPlacing extends GameError
{
    const CODE_INVALID_GAME_STATE = 100;
    const CODE_CANNOT_ADD_MORE_SHIPS = 101;
    const CODE_SHIP_OUT_OF_BOUNDARIES = 102;
    const CODE_UPDATING_NOT_OWNED_SHIP = 103;

    public static function invalidGameState($state)
    {
        return new self("cannot place ship during state[$state]", static::CODE_INVALID_GAME_STATE);
    }

    public static function cannotAddMoreShips($shipSize, $count)
    {
      return new self("cannot add more ships of size '$shipSize'; existing: '$count'", static::CODE_CANNOT_ADD_MORE_SHIPS);
    }

    public static function shipOutOfBoundaries(Ship $ship)
    {
      return new self("ship[{$ship->getX()},{$ship->getY()}] out of boundaries", static::CODE_SHIP_OUT_OF_BOUNDARIES);
    }

    public static function updatingNotOwnedShip($cheaterId, $realOwnerId)
    {
      return new self("user[$cheaterId] tried to steal ship from user[$realOwnerId]", static::CODE_UPDATING_NOT_OWNED_SHIP);
    }
}
