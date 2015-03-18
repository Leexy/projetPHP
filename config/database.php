<?php
$config['db'] = [
  'host' => 'localhost',
  'name' => 'battleship',
  'user' => 'battleship',
  'pass' => 'battleship',
  'options' => [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
  ],
];
