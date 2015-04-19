<?php
require 'vendor/autoload.php';
require 'bootstrap.php';

use Repository\GameRepository;
use Repository\Error\FullGame;
use Entity\Game;
use Service\Game\Hit as HitService;
use Service\Game\Error\Hit as HitError;
use Service\Game\ShipPlacing;
use Service\Game\Error\ShipPlacing as ShipPlacingError;
use Service\Game\Ready;
use Service\Game\Error\Ready as ReadyError;

use Repository\UserRepository;
use Entity\User;
use Service\User\Signup;

use Repository\ShipRepository;
use Entity\Ship;

use Repository\HitRepository;
use Entity\Hit;

$app->post('/games/:id/place-ship', function ($gameId) use($app) {
  $app->response->headers->set('Content-Type', 'application/json');
  $gameRepository = new GameRepository($app->dbh);
  $game = $gameRepository->fetchById($gameId);
  $ship = new Ship(json_decode($app->request->getBody(), true));
  $shipPlacingService = new ShipPlacing();
  $shipPlacingService->setGame($game);
  $shipPlacingService->setOwner($app->user);
  $shipPlacingService->setShipRepository(new ShipRepository($app->dbh));
  try {
    $result = $shipPlacingService->handle($ship);
    $app->response->setBody(json_encode($result));
  } catch (ShipPlacingError $error) {
    switch ($error->getCode()) {
    case ShipPlacingError::CODE_INVALID_GAME_STATE:
      $app->halt(403);
      break;
    default:
      throw $error;
      break;
    }
  }
})->name('games.place-ship');

$app->post('/games/:id/hits', function ($gameId) use($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $hit = new Hit(json_decode($app->request->getBody(), true));
    $gameRepository = new GameRepository($app->dbh);
    $game = $gameRepository->fetchById($gameId);
    $hitService = new HitService();
    $hitService->setGame($game);
    $hitService->setShooter($app->user);
    $hitService->setGameRepository($gameRepository);
    $hitService->setHitRepository(new HitRepository($app->dbh));
    $hitService->setShipRepository(new ShipRepository($app->dbh));
    try {
        $result = $hitService->handle($hit);
        $app->response->setBody(json_encode($result));
    } catch (HitError $error) {
        switch ($error->getCode()) {
            case HitError::CODE_NOT_PLAYING:
            case HitError::CODE_NOT_YOUR_TURN:
                $app->halt(403);
                break;
            default:
                throw $error;
        }
    }
})->name('game.hit');

$app->post('/games/:id/ready', function ($gameId) use($app) {
  $app->response->headers->set('Content-Type', 'application/json');
  $gameRepository = new GameRepository($app->dbh);
  $game = $gameRepository->fetchById($gameId);
  $gameReadyService = new Ready();
  $gameReadyService->setGameRepository($gameRepository);
  $gameReadyService->setShipRepository(new ShipRepository($app->dbh));
  $gameReadyService->setGame($game);
  $gameReadyService->setPlayer($app->user);
  try {
    $gameReadyService->handle();
    $app->response->setStatus(200);
  } catch (ReadyError $error) {
    switch ($error->getCode()) {
    case ReadyError::CODE_USER_NOT_IN_GAME:
      $app->halt(403);
      break;
    case ReadyError::CODE_INVALID_GAME_STATE:
      $app->log->debug("trying to get ready whereas game state is '{$game->getState()}'");
      $app->response->setStatus(403);
      break;
    case ReadyError::CODE_FLEET_NOT_READY:
      $app->log->debug("trying to get ready whereas the fleet is not");
      $app->response->setStatus(403);
      break;
    default:
      throw $error;
      break;
    }
  }
})->name('game.ready');

$app->get('/games/:id/state', function ($gameId) use($app) {
  $app->response->headers->set('Content-Type', 'application/json');
  $user = $app->user;
  $gameRepository = new GameRepository($app->dbh);
  $game = $gameRepository->fetchById($gameId);
  if (!$game->isPlaying($user)) {
    $app->redirect($app->urlFor('games.list'));
  }
  $response = ['state' => $game->getState()];
  if ($game->getState() === Game::STATE_PLAYING) {
    $response['play'] = $game->isPlayerTurn($user);
  }

  $app->response->setBody(json_encode($response));
})->name('game.state');

$app->get('/games/:id', function ($gameId) use($app) {
  $user = $app->user;
  $userRepository = new UserRepository($app->dbh);
  $gameRepository = new GameRepository($app->dbh);
  $game = $gameRepository->fetchById($gameId);
  if (!$game->isPlaying($user)) {
    if ($game->getUser2Id()) {
      $app->redirect($app->urlFor('games.list'));
    } else {
      try {
        $gameRepository->tryToAddUser($user, $game);
      } catch (FullGame $error) {
        $app->flash('error', "Sorry, somebody already joined this game. :(");
        $app->redirect($app->urlFor('games.list'));
      }
    }
  }
  $opponent = null;
  $opponentId = $game->getOpponentIdOf($user);
  if($opponentId){
    $opponent = $userRepository->fetchById($opponentId);
  }
  $app->render('game.html.twig', [
    'game' => $game,
    'user' => $user,
    'opponent' => $opponent,
    'states' => [
      'WAITING' => Game::STATE_WAITING,
      'PLACING' => Game::STATE_PLACING,
      'PLAYING' => Game::STATE_PLAYING,
      'FINISHED' => Game::STATE_FINISHED,
    ],
  ]);
})->name('game');

$app->get('/games', function () use($app) {
  $gameRepository = new GameRepository($app->dbh);
  $games = $gameRepository->fetchWaiting();
  $app->render('games.html.twig', ['games' => $games]);
})->name('games.list');

$app->get('/user/profile', function () use($app) {
  $gameRepository = new GameRepository($app->dbh);
  $victoryCount = $gameRepository->getVictoryCountFor($app->user);
  $playedCount = $gameRepository->getPlayedCountFor($app->user);
  $app->render('user-profile.html.twig', [
    'display_name' => $app->user->getDisplayName(),
    'email' => $app->user->getEmail(),
    'victory_count' => $victoryCount,
    'played_count' => $playedCount,
  ]);
})->name('user.profile');

$app->get('/top10', function () use($app) {
  $userRepository = new UserRepository($app->dbh);
  $topTen = $userRepository->fetchTopTen();
  $app->render('top10.html.twig', ['top_ten' => $topTen]);
})->name('top10');


$app->get('/user/games', function () use($app) {
  $userRepository = new UserRepository($app->dbh);
  $gameRepository = new GameRepository($app->dbh);
  $awaitingGames = $gameRepository->fetchWaitingFor($app->user);
  $startedGames = $gameRepository->fetchStartedFor($app->user);
  $opponents = [];
  foreach ($startedGames as $game) {
    $gameId = $game->getId();
    $opponents[$gameId] = $userRepository->fetchById($game->getOpponentIdOf($app->user));
  }
  $app->render('user-games.html.twig', [
    'awaiting_games' => $awaitingGames,
    'started_games' => $startedGames,
    'opponents' => $opponents,
  ]);
})->name('user.games.list');

$app->post('/games', function () use($app) {
  $gameRepository = new GameRepository($app->dbh);
  try {
    $gameId = $gameRepository->createFor($app->user);
    $app->redirect($app->urlFor('game', ['id' => $gameId]));
  } catch (\Repository\Error\GameCreationLimit $error) {
    $app->flash('error', "Heyy! You already have a game awaiting! Please be patient. :)");
    $app->render('games.html.twig', ['games' => $gameRepository->fetchWaiting()]);
  }
})->name('games.create');

$app->get('/', function () use($app) {
  if ($app->userRole === User::ROLE_GUEST) {
    $app->redirectTo('login.page');
  } else {
    $app->redirectTo('user.games.list');
  }
})->name('home');

$app->get('/signup', function () use($app) {
  $app->render('signup.html.twig');
})->name('signup.page');

$app->post('/signup', function () use($app) {
  $displayName = $app->request->post('display-name', '');
  $email = $app->request->post('email', '');
  $password = $app->request->post('password', '');
  $passwordConfirmation = $app->request->post('password-confirm', '');
  $signupService = new Signup();
  $signupService->setUserRepository(new UserRepository($app->dbh));
  $signupService->setDisplayName($displayName);
  $signupService->setEmail($email);
  $signupService->setPassword($password);
  $signupService->setPasswordConfirmation($passwordConfirmation);
  $signupService->proceed();
  if ($signupService->hasError()) {
    $app->flash('error', [
      Signup::ERROR_NO_DATA => 'Please fill up the form!',
      Signup::ERROR_INVALID_EMAIL => 'You might have mistyped your e-mail address… please try again.',
      Signup::ERROR_BAD_CONFIRMATION => 'You might have mistyped your password… please try again.',
      Signup::ERROR_QUERY_FAILED => 'Wow, we\'re sorry. An error occurred, so please try again later.',
      Signup::ERROR_EMAIL_ALREADY_USED => 'An account with this e-mail already exists! Try to login now. :-)',
    ][$signupService->getError()]);
    $emailQueryString = '?'. http_build_query(['email' => $email]);
    if ($signupService->getError() === Signup::ERROR_EMAIL_ALREADY_USED) {
      $app->redirect($app->urlFor('login.page') . $emailQueryString);
    } else {
      $app->redirect($app->urlFor('signup.page') . $emailQueryString);
    }
  } else {
    $app->authenticator->authenticate($email, $password);
    $app->redirect($app->urlFor('home'));
  }
})->name('signup.process');

$app->get('/login', function () use($app) {
  $redirectTo = $app->request->get('redirect_to', '/');
  $email = $app->request->get('email', '');
  $app->render('login.html.twig', [
    'email' => $email,
    'redirect_to' => $redirectTo,
  ]);
})->name('login.page');

$app->post('/login', function () use ($app) {
  $email = $app->request->post('email', '');
  $password = $app->request->post('password', '');
  $redirectTo = $app->request->post('redirect_to', '/');

  $authentication = $app->authenticator->authenticate($email, $password);
  if ($authentication->isValid()) {
    $app->redirect($redirectTo);
  }
  //$messages = $authentication->getMessages();
  //var_dump($messages);exit;
  $app->flash('error', 'Bummer! We could not authenticate you. Would you mind to check your credentials and try again?');
  $queryString = sprintf('?%s', http_build_query([
    'email' => $email,
    'redirect_to' => $redirectTo,
  ]));
  $app->redirect($app->urlFor('login.page') . $queryString);
})->name('login.process');

$app->get('/logout', function () use($app) {
  $app->authenticator->logout();
  $app->redirect($app->urlFor('home'));
})->name('logout.process');

$app->run();
