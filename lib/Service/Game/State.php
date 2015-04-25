<?php
namespace Service\Game;

use Repository\GameRepository;
use Repository\Error\Base as RepositoryError;
//TODO : utiliser ce service plutot que d'ecrire directement le code dans l'action
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
