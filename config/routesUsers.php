<?php

/**
 * config/routesUsers.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

use Slim\App;
use TDW\ACiencia\Controller\UserController;
use TDW\ACiencia\Middleware\JwtMiddleware;

/**
 * ############################################################
 * routes /api/v1/users
 * ############################################################
 * @param App $app
 */
return function (App $app) {

    $REGEX_USER_ID = '/{userId:[0-9]+}';
    $REGEX_USERNAME = '/{username:[a-zA-Z0-9()áéíóúÁÉÍÓÚñÑ %$\.+-]+}';

    // CGET: Returns all users
    $app->get(
        $_ENV['RUTA_API'] . UserController::PATH_USERS,
        UserController::class . ':cget'
    )->setName('tdw_users_cget')
        ->add(JwtMiddleware::class);

    // GET: Returns a user based on a single ID
    $app->get(
        $_ENV['RUTA_API'] . UserController::PATH_USERS . $REGEX_USER_ID,
        UserController::class . ':get'
    )->setName('tdw_users_get')
        ->add(JwtMiddleware::class);

    // GET: Returns status code 204 if username exists
    $app->get(
        $_ENV['RUTA_API'] . UserController::PATH_USERS . '/username' . $REGEX_USERNAME,
        UserController::class . ':getUsername'
    )->setName('tdw_users_get_username');

    // OPTIONS: Provides the list of HTTP supported methods
    $app->options(
        $_ENV['RUTA_API'] . UserController::PATH_USERS . '/username' . $REGEX_USERNAME,
        UserController::class . ':options'
    )->setName('tdw_users_exists_options');

    // DELETE: Deletes a user
    $app->delete(
        $_ENV['RUTA_API'] . UserController::PATH_USERS . $REGEX_USER_ID,
        UserController::class . ':delete'
    )->setName('tdw_users_delete')
        ->add(JwtMiddleware::class);

    // OPTIONS: Provides the list of HTTP supported methods
    $app->options(
        $_ENV['RUTA_API'] . UserController::PATH_USERS . '[' . $REGEX_USER_ID . ']',
        UserController::class . ':options'
    )->setName('tdw_users_options');

    // POST: Creates a new user
    $app->post(
        $_ENV['RUTA_API'] . UserController::PATH_USERS,
        UserController::class . ':post'
    )->setName('tdw_users_post')
        ->add(JwtMiddleware::class);

    // POST: Registers a new user
    $app->post(
        $_ENV['RUTA_API'] . UserController::PATH_USERS . '/register',
        UserController::class . ':register'
    )->setName('tdw_users_register_post');

    // OPTIONS: Provides the list of HTTP supported methods
    $app->options(
        $_ENV['RUTA_API'] . UserController::PATH_USERS . '/register',
        UserController::class . ':options'
    )->setName('tdw_users_register_options');

    // PUT: Updates a user
    $app->put(
        $_ENV['RUTA_API'] . UserController::PATH_USERS . $REGEX_USER_ID,
        UserController::class . ':put'
    )->setName('tdw_users_put')
        ->add(JwtMiddleware::class);
};
