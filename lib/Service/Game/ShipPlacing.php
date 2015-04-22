<?php
namespace Service\Game;


use Entity\Game;
use Entity\Ship;
use Entity\User;
use Repository\ShipRepository;
use Service\Game\Error\ShipPlacing as ShipPlacingError;

class ShipPlacing
{
  const SHIPS_OF_SIZE_2 = 1;
  const SHIPS_OF_SIZE_3 = 2;
  const SHIPS_OF_SIZE_4 = 1;
  const SHIPS_OF_SIZE_5 = 1;

  /**
   * @var Game
   */
  private $game;

  /**
   * @var User
   */
  private $owner;

  /**
   * @var ShipRepository
   */
  private $shipRepository;

  public function handle(Ship $ship)
  {
    if (!in_array($this->game->getState(), [Game::STATE_WAITING, Game::STATE_PLACING, Game::STATE_PLAYER1_READY, Game::STATE_PLAYER2_READY])) {
      throw ShipPlacingError::invalidGameState($this->game->getState());
    }
    if (!$this->isShipInBoundaries($ship)) {
      throw ShipPlacingError::shipOutOfBoundaries($ship);
    }
    if ($ship->isPersisted()) {
      if (!static::shipBelongsToUserAndGame($ship, $this->owner, $this->game)) {
        throw ShipPlacingError::updatingNotOwnedShip($this->owner->getId(), $ship->getOwnerId());
      }
      $this->shipRepository->update($ship);
    } else {
      $userShips = $this->shipRepository->fetchForUserInGame($this->owner, $this->game);
      if ($this->allowToAdd($ship, $userShips)) {
        $this->shipRepository->create($ship, $this->game, $this->owner);
      } else {
        throw ShipPlacingError::cannotAddMoreShips($ship->getSize(), $this->countShipsOfSameSize($ship, $userShips));
      }
    }
  }

  protected function shipBelongsToUserAndGame(Ship $ship, User $user, Game $game)
  {
    return $ship->getOwnerId() === $user->getId() and $ship->getGameId() === $game->getId();
  }

  protected function isShipInBoundaries(Ship $ship)
  {
    $isShipInBoundaries = true;
    if ($ship->getX() < 1 or $ship->getX() > 10) {
      $isShipInBoundaries = false;
    } else if ($ship->getY() < 1 or $ship->getY() > 10) {
      $isShipInBoundaries = false;
    } else if ($ship->isHorizontal() and $ship->getX() > (10 - ($ship->getSize() - 1))) {
      $isShipInBoundaries = false;
    } else if ($ship->isVertical() and $ship->getY() > (10 - ($ship->getSize() - 1))) {
      $isShipInBoundaries = false;
    }
    return $isShipInBoundaries;
  }

  /**
   * @param Ship $ship
   * @param Ship[] $fleet
   * @return bool
   */
  protected function allowToAdd(Ship $ship, array $fleet)
  {
    $currentCountInFleet = $this->countShipsOfSameSize($ship, $fleet);
    return $currentCountInFleet < static::maxQuantityOfShipsForSize($ship->getSize());
  }

  public static function getFleetSize()
  {
    return (
      static::SHIPS_OF_SIZE_2 +
      static::SHIPS_OF_SIZE_3 +
      static::SHIPS_OF_SIZE_4 +
      static::SHIPS_OF_SIZE_5
    );
  }

  /**
   * @param int $size
   * @return int Allowed quantity of ships of size $size in a fleet.
   */
  public static function maxQuantityOfShipsForSize($size)
  {
    switch ($size) {
    case 2:
      return static::SHIPS_OF_SIZE_2;
      break;
    case 3:
      return static::SHIPS_OF_SIZE_3;
      break;
    case 4:
      return static::SHIPS_OF_SIZE_4;
      break;
    case 5:
      return static::SHIPS_OF_SIZE_5;
      break;
    default:
      return 0;
      break;
    }
  }

  /**
   * @param Ship $ship
   * @param Ship[] $fleet
   * @return int
   */
  protected function countShipsOfSameSize(Ship $ship, array $fleet)
  {
    $count = 0;
    foreach ($fleet as $fleetShip) {
      if ($ship->getSize() === $fleetShip->getSize()) {
        ++$count;
      }
    }
    return $count;
  }

  /**
   * @param Game $game
   */
  public function setGame($game)
  {
    $this->game = $game;
  }

  /**
   * @param User $owner
   */
  public function setOwner($owner)
  {
    $this->owner = $owner;
  }

  /**
   * @param ShipRepository $shipRepository
   */
  public function setShipRepository($shipRepository)
  {
    $this->shipRepository = $shipRepository;
  }
}
