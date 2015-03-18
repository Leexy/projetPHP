<?php
require 'config/app.php';
require 'config/database.php';
require 'config/acl.php';

use Entity\User;

use JeremyKendall\Password\PasswordValidator;
use JeremyKendall\Slim\Auth\Adapter\Db\PdoAdapter;
use JeremyKendall\Slim\Auth\Bootstrap;

use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Authentication\Storage\Session as SessionStorage;

$app = new \Slim\Slim($config['slim']);

$app->dbh = new \PDO(
  sprintf('mysql:dbname=%s;host=%s', $config['db']['name'], $config['db']['host']),
  $config['db']['user'],
  $config['db']['pass'],
  $config['db']['options']
);

$adapter = new PdoAdapter($app->dbh, 'users', 'email', 'password', new PasswordValidator());

$sessionConfig = new SessionConfig();
$sessionConfig->setOptions([
  'remember_me_seconds' => 60 * 60 * 24 * 7,
  'name' => 'slim-auth-impl',
]);

$sessionManager = new SessionManager($sessionConfig);
$sessionManager->rememberMe();

$authBootstrap = new Bootstrap($app, $adapter, new Acl());
$authBootstrap->setStorage(new SessionStorage(null, null, $sessionManager));
$authBootstrap->bootstrap();

//unset($adapter, $sessionConfig, $sessionManager, $authBootstrap);

$app->hook('slim.before.dispatch', function () use ($app) {
  $user = $app->auth->hasIdentity() ? new User($app->auth->getIdentity()) : null;
  $app->user = $user;
  $app->userRole = $user ? $user->getRole() : 'guest';
  $app->view->appendData([
    'user' => $app->user,
    'user_role' => $app->userRole,
  ]);
});

