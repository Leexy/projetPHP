<?php
namespace Service\Game;

use Repository\GameRepository;
use Repository\Error\Base as RepositoryError;

class State
{
  /**
   * @var GameRepository
   */
  private $gameRepository;

  /**
   * @param GameRepository $gameRepository
   */
  public function setGameRepository($gameRepository)
  {
    $this->GameRepository = $gameRepository;
  }
}
