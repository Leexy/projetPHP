<?php
$config = [];

$config['slim'] = [];

$viewEngine = new \Slim\Views\Twig();
$viewEngine->parserOptions = [
  'debug' => true,
];
$viewEngine->parserExtensions = [
  new \Slim\Views\TwigExtension(),
];

$config['slim']['view'] = $viewEngine;
