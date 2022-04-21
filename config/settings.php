<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Timezone
date_default_timezone_set('Europe/Madrid');

// Settings
$settings = [];

// Path settings
$settings['root'] = dirname(__DIR__);
$settings['temp'] = $settings['root'] . '/var';
$settings['public'] = $settings['root'] . '/public';

// Error Handling Middleware settings
$settings['error_handler_middleware'] = [
    // Should be set to false in production
    'display_error_details' => true,
    // Parameter is passed to the default ErrorHandler
    // View in rendered output by enabling the "displayErrorDetails" setting.
    // For the console and unit tests it should be disable too
    'log_errors' => true,
    // Display error details in error log
    'log_error_details' => true,
];

// Application settings
$settings['app'] = [
    'secret' => $_ENV['JWT_SECRET'],
];

// Logger settings
$settings['logger'] = [
    'name' => 'app',
    'path' => $settings['temp'] . '/logs',
    'filename' => 'app.log',
    'level' => \Monolog\Logger::DEBUG,
    'file_permission' => 0775,
];

// JWT
$settings['jwt'] = [

    // The issuer name
    'issuer' => 'tdw-upm',

    // OAuth2: client-id
    'client-id' => 'upm-tdw-aciencia',

    // Max lifetime in seconds
    'lifetime' => 14400,

    // The private key
    'private_key_file' => __DIR__ . '/private.pem',

    // The public key
    'public_key_file' => __DIR__ . '/public.pem',
];

// Load environment configuration
//if (file_exists(__DIR__ . '/../../env.php')) {
//    require __DIR__ . '/../../env.php';
//} elseif (file_exists(__DIR__ . '/env.php')) {
//    require __DIR__ . '/env.php';
//}

// Unit-test and integration environment (Travis CI)
//if (defined('APP_ENV')) {
//    require __DIR__ . basename(APP_ENV) . '.php';
//}

return $settings;
