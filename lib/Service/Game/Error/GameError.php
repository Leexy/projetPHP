<?php
namespace Service\Game\Error;


use Entity\Game;
use Entity\User;

class GameError extends \RuntimeException
{
  const CODE_USER_NOT_IN_GAME = 1;

  public static function userNotInGame(User $user, Game $game)
  {
    return new static("error: user[{$user->getId()}] is not in game[{$game->getId()}]", static::CODE_USER_NOT_IN_GAME);
  }
}
