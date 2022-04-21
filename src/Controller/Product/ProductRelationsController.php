<?php

/**
 * src/Controller/Product/ProductRelationsController.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Controller\Product;

use Doctrine\ORM\ORMException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;
use TDW\ACiencia\Controller\ElementRelationsBaseController;
use TDW\ACiencia\Controller\Entity\EntityController;
use TDW\ACiencia\Controller\Person\PersonController;

/**
 * Class ProductRelationsController
 */
final class ProductRelationsController extends ElementRelationsBaseController
{

    /**
     * @inheritDoc
     */
    public static function getEntityClassName(): string
    {
        return ProductController::getEntityClassName();
    }

    /**
     * @inheritDoc
     */
    public static function getEntitiesTag(): string
    {
        return ProductController::getEntitiesTag();
    }

    /**
     * @inheritDoc
     */
    public static function getEntityIdName(): string
    {
        return ProductController::getEntityIdName();
    }

    /**
     * Summary: GET /products/{productId}/entities
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function getEntities(Request $request, Response $response, array $args): Response
    {
        $elementData = [
            'getter' => 'getEntities',
            'stuff' => EntityController::getEntitiesTag(),
        ];
        return $this->getElements($response, $args, $elementData);
    }

    /**
     * PUT /products/{productId}/entities/add/{stuffId}
     * PUT /products/{productId}/entities/rem/{stuffId}
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     * @throws ORMException
     */
    public function operationEntity(Request $request, Response $response, array $args): Response
    {
        $elementData = [
            'stuffEName' => EntityController::getEntityClassName(),
            'stuffId' => $args['stuffId'],
            'getter' => 'getEntities',
            'stuff' => EntityController::getEntitiesTag(),
        ];
        return $this->operationStuff($request, $response, $args, $elementData);
    }

    /**
     * Summary: GET /products/{productId}/persons
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function getPersons(Request $request, Response $response, array $args): Response
    {
        $elementData = [
            'getter' => 'getPersons',
            'stuff' => PersonController::getEntitiesTag(),
        ];
        return $this->getElements($response, $args, $elementData);
    }

    /**
     * PUT /products/{productId}/persons/add/{stuffId}
     * PUT /products/{productId}/persons/rem/{stuffId}
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     * @throws ORMException
     */
    public function operationPerson(Request $request, Response $response, array $args): Response
    {
        $elementData = [
            'stuffEName' => PersonController::getEntityClassName(),
            'stuffId' => $args['stuffId'],
            'getter' => 'getPersons',
            'stuff' => PersonController::getEntitiesTag(),
        ];
        return $this->operationStuff($request, $response, $args, $elementData);
    }
}
