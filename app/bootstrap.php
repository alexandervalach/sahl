<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode('147.175.177.65');
$configurator->enableDebugger(__DIR__ . '/../log');
error_reporting(~E_USER_DEPRECATED);

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
        ->addDirectory(__DIR__)
        ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
