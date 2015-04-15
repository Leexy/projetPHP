<?php
namespace Service\Game;

use Entity\User;

use Repository\GameRepository;
use Entity\Game;
use Service\Game\Error\Hit as HitError;

use Repository\HitRepository;
use Entity\Hit as HitEntity;

use Repository\ShipRepository;
use Entity\Ship;

class Hit
{
    /**
     * @var GameRepository
     */
    private $gameRepository;

    /**
     * @var HitRepository
     */
    private $hitRepository;

    /**
     * @var ShipRepository
     */
    private $shipRepository;

    /**
     * @var Game
     */
    private $game;

    /**
     * @var User
     */
    private $shooter;

    public function handle(HitEntity $hit)
    {
        if ($this->game->getState() !== Game::STATE_PLAYING) {
            throw HitError::notPlaying($this->game, $this->shooter);
        }
        if (!$this->game->isPlayerTurn($this->shooter)) {
            throw HitError::notYourTurn($this->shooter);
        }
        $hit->setGameId($this->game->getId());
        $hit->setUserId($this->shooter->getId());
        $this->hitRepository->create($hit);
        $success = false;
        foreach ($this->getOpponentShips() as $opponentShip) {
            if ($opponentShip->isHitBy($hit)) {
                $success = true;
                break;
            }
        }
        $this->gameRepository->switchPlayingUser($this->game);
        return array_merge(['success' => $success], $hit->getPosition());
    }

    /**
     * @return Ship[]
     */
    protected function getOpponentShips()
    {
        return $this->shipRepository->fetchForUserInGame(
            new User($this->game->getOpponentIdOf($this->shooter)),
            $this->game
        );
    }

    /**
     * @param Game $game
     */
    public function setGame($game)
    {
        $this->game = $game;
    }

    /**
     * @param User $shooter
     */
    public function setShooter($shooter)
    {
        $this->shooter = $shooter;
    }

    /**
     * @param HitRepository $hitRepository
     */
    public function setHitRepository($hitRepository)
    {
        $this->hitRepository = $hitRepository;
    }

    /**
     * @param ShipRepository $shipRepository
     */
    public function setShipRepository($shipRepository)
    {
        $this->shipRepository = $shipRepository;
    }

    /**
     * @param GameRepository $gameRepository
     */
    public function setGameRepository($gameRepository)
    {
        $this->gameRepository = $gameRepository;
    }
}
