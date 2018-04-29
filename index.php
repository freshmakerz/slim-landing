<?php

require 'vendor/autoload.php';

define('PRISMIC_URL', 'https://aream-le-plaine-monceau.cdn.prismic.io/api');

use \Prismic\Api;
use \Prismic\LinkResolver;
use \Prismic\Predicates;
use \Mailgun\Mailgun;

// default settings
$config = [
    'settings' => [
        'cache' => true,
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'c9',
            'username' => 'freshmakerz',
            'password' => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]
    ]
];

// Init app
$app = new \Slim\App($config);

$container = $app->getContainer();

// Set logger
$container['logger'] = function($container) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler('logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

// Set view
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('views', [
        'cache' => $container['settings']['cache'] ? 'cache/views' : false
    ]);
    
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};

// Set DB
$container['db'] = function ($container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;

    $capsule->addConnection($container['settings']['db']);

    $capsule->setAsGlobal();

    return $capsule;
};

// Cache layer
$container['cache'] = function () {
    return new \Slim\HttpCache\CacheProvider();
};

$app->add(new \Slim\HttpCache\Cache('public', 86400));

$app->get('/', function ($request, $response, $args) {
    $api = Api::get(PRISMIC_URL);
    $slider = $api->getSingle('slider');
    
    return $this->view->render($response, 'templates/home.html', [
        'page_name' => 'home',
        'slider' => $slider
    ]);
});

$app->get('/les-3-adresses', function ($request, $response, $args) {
    return $this->view->render($response, 'templates/les-3-adresses.html', [
        'page_name' => 'les-3-adresses'
    ]);
});

$app->get('/les-signatures', function ($request, $response, $args) {
    return $this->view->render($response, 'templates/les-signatures.html', [
        'page_name' => 'les-signatures'
    ]);
});

$app->post('/contact', function($request, $response, $argv) use($container) {
    // $container->get('db')->table('contacts')->get();
    $mg = Mailgun::create('key-example');
});

$app->run();