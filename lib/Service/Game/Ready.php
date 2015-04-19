<?php
namespace Service\Game;

use Service\Game\Error\Ready as ReadyError;

use Entity\User;

use Repository\GameRepository;
use Entity\Game;

use Repository\ShipRepository;
use Entity\Ship;

class Ready extends Base
{
  /**
   * @var GameRepository
   */
  private $gameRepository;

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
  private $player;

  public function handle()
  {
    static::checkUserIsInGame($this->player, $this->game);
    $allowedStates = [Game::STATE_WAITING, Game::STATE_PLACING];
    if ($this->isPlayer1()) {
      $allowedStates[] = Game::STATE_PLAYER2_READY;
    } else {
      $allowedStates[] = Game::STATE_PLAYER1_READY;
    }
    if (!in_array($this->game->getState(), $allowedStates)) {
      throw ReadyError::invalidGameState($this->game->getState());
    }
    $this->checkFleetIsReady();
    $this->gameRepository->upgradeStateToReady($this->game, $this->isPlayer1());
  }

  protected function checkFleetIsReady()
  {
    $fleet = $this->getPlayerFleet();
    $shipsCount = count($fleet);
    if ($shipsCount !== ShipPlacing::getFleetSize()) {
      throw ReadyError::fleetNotReady($this->player, $shipsCount);
    }
  }

  protected function getPlayerFleet()
  {
    return $this->shipRepository->fetchForUserInGame($this->player, $this->game);
  }

  protected function isPlayer1()
  {
    return $this->player->getId() === $this->game->getUser1Id();
  }

  /**
   * @param Game $game
   */
  public function setGame($game)
  {
    $this->game = $game;
  }

  /**
   * @param User $player
   */
  public function setPlayer($player)
  {
    $this->player = $player;
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
