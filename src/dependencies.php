<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// Service factory for the ORM
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);

$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function ($container) {
    return $capsule;
};

// login controller
$container['LoginController'] = function ($container) {
	return new \App\Controllers\LoginController($container);
};

// projects controller
$container['ProjectsController'] = function ($container) {
	return new \App\Controllers\ProjectsController($container);
};

// customers controller
$container['CustomersController'] = function ($container) {
	return new \App\Controllers\CustomersController($container);
};

// sales controller
$container['SalesController'] = function ($container) {
	return new \App\Controllers\SalesController($container);
};

// sales reports controller
$container['SalesReportsController'] = function ($container) {
	return new \App\Controllers\SalesReportsController($container);
};