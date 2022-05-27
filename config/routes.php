<?php

/**
 * config/routes.php - Define app routes
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

use TDW\ACiencia\Controller\LoginController;
use Slim\App;

return function (App $app) {

    // Redirection / -> /api-docs/index.html
    $app->redirect(
        '/',
        '/api-docs/index.html'
    )->setName('tdw_home_redirect');


    /**
     * ############################################################
     * routes /access_token
     * POST /access_token
     * ############################################################
     */
    $app->post(
        $_ENV['RUTA_LOGIN'],
        LoginController::class . ':post'
    )->setName('tdw_post_login');

    /**
     * ############################################################
     * routes /access_token
     * OPTIONS /access_token
     * ############################################################
     */
    $app->options(
        $_ENV['RUTA_LOGIN'],
        LoginController::class . ':options'
    )->setName('tdw_options_login');

};
