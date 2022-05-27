<?php

/**
 * config/routesProducts.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

use Slim\App;
use TDW\ACiencia\Controller\Product\ProductController;
use TDW\ACiencia\Controller\Product\ProductRelationsController;
use TDW\ACiencia\Middleware\JwtMiddleware;

/**
 * ############################################################
 * routes /api/v1/products
 * ############################################################
 * @param App $app
 */
return function (App $app) {

    $REGEX_PRODUCT_ID = '/{productId:[0-9]+}';
    $REGEX_STUFF_ID = '/{stuffId:[0-9]+}';
    $REGEX_PRODUCT_NAME = '[a-zA-Z0-9()áéíóúÁÉÍÓÚñÑ %$\.+-]+';

    // CGET: Returns all products
    $app->get(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS,
        ProductController::class . ':cget'
    )->setName('readProducts')
        ->add(JwtMiddleware::class);

    // GET: Returns a product based on a single ID
    $app->get(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS . $REGEX_PRODUCT_ID,
        ProductController::class . ':get'
    )->setName('readProduct')
        ->add(JwtMiddleware::class);

    // GET: Returns status code 204 if productname exists
    $app->get(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS . '/productname/{productname:' . $REGEX_PRODUCT_NAME . '}',
        ProductController::class . ':getProductname'
    )->setName('existsProduct');

    // DELETE: Deletes a product
    $app->delete(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS . $REGEX_PRODUCT_ID,
        ProductController::class . ':delete'
    )->setName('deleteProduct')
        ->add(JwtMiddleware::class);

    // OPTIONS: Provides the list of HTTP supported methods
    $app->options(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS . '[' . $REGEX_PRODUCT_ID . ']',
        ProductController::class . ':options'
    )->setName('optionsProduct');

    // POST: Creates a new product
    $app->post(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS,
        ProductController::class . ':post'
    )->setName('createProduct')
        ->add(JwtMiddleware::class);

    // PUT: Updates a product
    $app->put(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS . $REGEX_PRODUCT_ID,
        ProductController::class . ':put'
    )->setName('updateProduct')
        ->add(JwtMiddleware::class);

    // RELATIONSHIPS

    // GET /products/{productId}/entities
    $app->get(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS . $REGEX_PRODUCT_ID . '/entities',
        ProductRelationsController::class . ':getEntities'
    )->setName('readProductEntities')
        ->add(JwtMiddleware::class);

    // PUT /products/{productId}/entities/add/{stuffId}
    $app->put(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS . $REGEX_PRODUCT_ID . '/entities/add' . $REGEX_STUFF_ID,
        ProductRelationsController::class . ':operationEntity'
    )->setName('tdw_products_add_entity')
        ->add(JwtMiddleware::class);

    // OPTIONS /products/{productId}/entities/add/{stuffId}
    $app->options(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS . $REGEX_PRODUCT_ID . '/entities/add' . $REGEX_STUFF_ID,
        ProductRelationsController::class . ':options'
    )->setName('optionsProductAddEntity');

    // PUT /products/{productId}/entities/rem/{stuffId}
    $app->put(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS . $REGEX_PRODUCT_ID . '/entities/rem' . $REGEX_STUFF_ID,
        ProductRelationsController::class . ':operationEntity'
    )->setName('tdw_products_rem_entity')
        ->add(JwtMiddleware::class);

    // OPTIONS /products/{productId}/entities/rem/{stuffId}
    $app->options(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS . $REGEX_PRODUCT_ID . '/entities/rem' . $REGEX_STUFF_ID,
        ProductRelationsController::class . ':options'
    )->setName('optionsProductRemEntity');

    // GET /products/{productId}/persons
    $app->get(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS . $REGEX_PRODUCT_ID . '/persons',
        ProductRelationsController::class . ':getPersons'
    )->setName('readProductPersons')
        ->add(JwtMiddleware::class);

    // PUT /products/{productId}/persons/add/{stuffId}
    $app->put(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS . $REGEX_PRODUCT_ID
        . '/persons/add' . $REGEX_STUFF_ID,
        ProductRelationsController::class . ':operationPerson'
    )->setName('tdw_products_add_person')
        ->add(JwtMiddleware::class);

    // OPTIONS /products/{productId}/persons/add/{stuffId}
    $app->options(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS . $REGEX_PRODUCT_ID
        . '/persons/add' . $REGEX_STUFF_ID,
        ProductRelationsController::class . ':options'
    )->setName('optionsProductAddPerson');

    // PUT /products/{productId}/persons/rem/{stuffId}
    $app->put(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS . $REGEX_PRODUCT_ID
        . '/persons/rem' . $REGEX_STUFF_ID,
        ProductRelationsController::class . ':operationPerson'
    )->setName('tdw_products_rem_person')
        ->add(JwtMiddleware::class);

    // OPTIONS /products/{productId}/persons/rem/{stuffId}
    $app->options(
        $_ENV['RUTA_API'] . ProductController::PATH_PRODUCTS . $REGEX_PRODUCT_ID
        . '/persons/rem' . $REGEX_STUFF_ID,
        ProductRelationsController::class . ':options'
    )->setName('optionsProductRemPerson');
};
