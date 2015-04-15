<?php
namespace Service\Game\Error;


use Entity\Game;
use Entity\User;

class Hit extends GameError
{
    const CODE_NOT_YOUR_TURN = 100;
    const CODE_NOT_PLAYING = 101;

    public static function notPlaying(Game $game, User $shooter)
    {
        return new self(
            sprintf(
                "hit from user[%s] while not playing (state[%s])",
                $shooter->getId(),
                $game->getState()
            ),
            self::CODE_NOT_PLAYING
        );
    }

    public static function notYourTurn(User $user)
    {
        return new self(
            sprintf("user[%s] tried to played but it was not their turn", $user->getId()),
            self::CODE_NOT_YOUR_TURN
        );
    }
}
