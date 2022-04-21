<?php

/**
 * src/Controller/Product/ProductController.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Controller\Product;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;
use TDW\ACiencia\Controller\ElementBaseController;
use TDW\ACiencia\Entity\Product;

/**
 * Class ProductController
 */
class ProductController extends ElementBaseController
{
    /** @var string ruta api gestión productos  */
    public const PATH_PRODUCTS = '/products';

    /**
     * @inheritDoc
     */
    public static function getEntitiesTag(): string
    {
        return 'products';
    }

    /**
     * @inheritDoc
     */
    public static function getEntityClassName(): string
    {
        return Product::class;
    }

    /**
     * @inheritDoc
     */
    public static function getEntityIdName(): string
    {
        return 'productId';
    }

    /**
     * Summary: Returns status code 204 if productname exists
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function getProductname(Request $request, Response $response, array $args): Response
    {
        return $this->getElementByName($response, $args['productname']);
    }
}
