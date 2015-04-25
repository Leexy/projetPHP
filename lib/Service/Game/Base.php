<?php
namespace Service\Game;

use Entity\Game;
use Entity\User;
use Service\Game\Error\GameError;
//classe de base des services de Game
class Base
{
  protected static function checkUserIsInGame(User $user, Game $game)
  {
    if (!in_array($user->getId(), [$game->getUser1Id(), $game->getUser2Id()])) {
      throw GameError::userNotInGame($user, $game);
    }
  }
}
