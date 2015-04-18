<?php
require 'config/app.php';
require 'config/database.php';
require 'config/acl.php';

use JeremyKendall\Slim\Auth\Exception\HttpUnauthorizedException;
use JeremyKendall\Slim\Auth\Exception\HttpForbiddenException;

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

$app->error(function (\Exception $error) use($app) {
    if ($error instanceof HttpUnauthorizedException) {
        $app->log->info(sprintf('401 uri[%s]', $app->request->getResourceUri()));
        if ($app->request->isXhr()) {
            $app->halt(401);
        } else {
            $queryString = sprintf('?%s', http_build_query([
                'redirect_to' => $app->request->getResourceUri(),
            ]));
            $app->redirect($app->urlFor('login.page') . $queryString);
        }
    } else if ($error instanceof HttpForbiddenException) {
        $app->log->info(sprintf('403 user[%d] uri[%s]', $app->user->getId(), $app->request->getResourceUri()));
        if ($app->request->isXhr()) {
            $app->halt(403);
        } else {
            $app->render('error/403.html.twig', ['error' => $error], 403);
        }
    } else {
        $app->log->error(sprintf('500 user[%d] uri[%s] error[%s]', $app->user->getId(), $app->request->getResourceUri(), $error->getMessage()));
        if ($app->request->isXhr()) {
            $app->halt(500);
        } else {
            $app->render('error/500.html.twig', ['error' => $error], 500);
        }
    }
});

