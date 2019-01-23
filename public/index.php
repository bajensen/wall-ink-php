<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$autoloadFile = '../vendor/autoload.php';

if (file_exists($autoloadFile)) {
    require $autoloadFile;
}
else {
    echo 'Autoload file not found. Run composer install.';
}

define('ROOT', realpath('..') . '/');

$env = defined('ENV') ? getenv('ENV') : 'development';

$defaultConfigFile = ROOT . 'config/config.default.php';
$envConfigFile = ROOT . 'config/config.' . $env . '.php';

$config = include($defaultConfigFile);

if (file_exists($envConfigFile)) {
    $config = array_replace_recursive($config, include($envConfigFile));
}

$appSettings = $config['app_settings'];
date_default_timezone_set($appSettings['timezone']);

$c = new \Slim\Container($config);
$c['view'] = function ($container) {
    return new \Slim\Views\PhpRenderer(ROOT . '/view/');
};

$app = new \Slim\App($c);
$app->add(new \RKA\Middleware\IpAddress($appSettings['use_proxy_headers'], $appSettings['trusted_proxies']));

if (isset($config['cas']['enabled']) && $config['cas']['enabled']) {
    $app->add(new \Wink\Auth\CasMiddleware($config['cas']));
}

$app->get('/', function (Request $request, Response $response, array $args) use ($app) {
    return $response->withRedirect('/admin');
});

$app->get('/display', function (Request $request, Response $response, array $args) use ($app) {
    $config = $app->getContainer()->get('wink_settings');
    $h = new \Wink\Handler\DisplayHandler($config, $app, $request, $response);
    return $h->display();
});

$app->get('/admin/preview', function (Request $request, Response $response, array $args) use ($app) {
    $config = $app->getContainer()->get('wink_settings');
    $previewHandler = new \Wink\Handler\PreviewHandler($config, $app, $request, $response);
    return $previewHandler->preview();
});

$app->get('/admin', function (Request $request, Response $response, array $args) use ($app) {
    $config = $app->getContainer()->get('wink_settings');
    $h = new \Wink\Handler\AdminHandler($config, $app, $request, $response);
    return $h->admin();
});

$app->get('/admin/devices', function (Request $request, Response $response, array $args) use ($app) {
    $config = $app->getContainer()->get('wink_settings');
    $h = new \Wink\Handler\AdminHandler($config, $app, $request, $response);
    return $h->devicesJson();
});

$app->get('/admin/device/{device_id}', function (Request $request, Response $response, array $args) use ($app) {
    $config = $app->getContainer()->get('wink_settings');
    $h = new \Wink\Handler\AdminHandler($config, $app, $request, $response);
    return $h->deviceJson($args['device_id']);
});

$app->post('/admin/device/{device_id}', function (Request $request, Response $response, array $args) use ($app) {
    $config = $app->getContainer()->get('wink_settings');
    $h = new \Wink\Handler\AdminHandler($config, $app, $request, $response);
    return $h->saveDevice($args['device_id']);
});

$app->delete('/admin/device/{device_id}', function (Request $request, Response $response, array $args) use ($app) {
    $config = $app->getContainer()->get('wink_settings');
    $h = new \Wink\Handler\AdminHandler($config, $app, $request, $response);
    return $h->deleteDevice($args['device_id']);
});


$app->get('/admin/device_history/{device_id}', function (Request $request, Response $response, array $args) use ($app) {
    $config = $app->getContainer()->get('wink_settings');
    $h = new \Wink\Handler\AdminHandler($config, $app, $request, $response);
    return $h->deviceHistoryJson($args['device_id']);
});

$app->get('/admin/sources', function (Request $request, Response $response, array $args) use ($app) {
    $config = $app->getContainer()->get('wink_settings');
    $h = new \Wink\Handler\AdminHandler($config, $app, $request, $response);
    return $h->sourcesJson();
});

$app->get('/admin/source_options', function (Request $request, Response $response, array $args) use ($app) {
    $config = $app->getContainer()->get('wink_settings');
    $h = new \Wink\Handler\AdminHandler($config, $app, $request, $response);
    return $h->pluginOptionsJson();
});

$app->get('/admin/layouts', function (Request $request, Response $response, array $args) use ($app) {
    $config = $app->getContainer()->get('wink_settings');
    $h = new \Wink\Handler\AdminHandler($config, $app, $request, $response);
    return $h->layoutsJson();
});

$app->get('/admin/layout_options', function (Request $request, Response $response, array $args) use ($app) {
    $config = $app->getContainer()->get('wink_settings');
    $h = new \Wink\Handler\AdminHandler($config, $app, $request, $response);
    return $h->layoutOptionsJson();
});

$app->get('/admin/complete_options', function (Request $request, Response $response, array $args) use ($app) {
    $config = $app->getContainer()->get('wink_settings');
    $h = new \Wink\Handler\AdminHandler($config, $app, $request, $response);
    return $h->completeOptionsJson();
});

$app->run();