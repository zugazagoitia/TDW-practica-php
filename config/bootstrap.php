<?php

use DI\ContainerBuilder;
use Slim\App;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(__DIR__ . '/container.php');

try {
    // Build PHP-DI Container instance
    $container = $containerBuilder->build();

    // Create App instance
    $app = $container->get(App::class);
} catch (Throwable $e) {
    fwrite(STDERR, 'EXCEPCIÓN: ' . $e->getCode() . ' - ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

// Register routes
(require __DIR__ . '/routes.php')($app);
(require __DIR__ . '/routesUsers.php')($app);
(require __DIR__ . '/routesProducts.php')($app);
(require __DIR__ . '/routesPersons.php')($app);
(require __DIR__ . '/routesEntities.php')($app);

// Register middleware
(require __DIR__ . '/middleware.php')($app);

return $app;
