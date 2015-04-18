<?php
$config = [];

$config['slim'] = [];

// Mettre à false pour que le gestionnaire d'erreur personnalisé soit utilisé,
// et donc que la redirection vers la page de login soit activée.
$config['slim']['debug'] = false;

$config['slim']['log.enabled'] = true;
$config['slim']['log.level'] = \Slim\Log::INFO;
$config['slim']['log.writer'] = new \Slim\Logger\DateTimeFileWriter(['path' => 'log']);

$viewEngine = new \Slim\Views\Twig();
$viewEngine->parserOptions = [
  'debug' => true,
];
$viewEngine->parserExtensions = [
  new \Slim\Views\TwigExtension(),
];

$config['slim']['view'] = $viewEngine;
