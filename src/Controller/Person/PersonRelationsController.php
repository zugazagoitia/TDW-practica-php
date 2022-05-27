<?php

/**
 * src/Controller/Person/PersonRelationsController.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Controller\Person;

use Doctrine\ORM\ORMException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;
use TDW\ACiencia\Controller\ElementRelationsBaseController;
use TDW\ACiencia\Controller\Entity\EntityController;
use TDW\ACiencia\Controller\Product\ProductController;

/**
 * Class PersonRelationsController
 */
final class PersonRelationsController extends ElementRelationsBaseController
{

    /**
     * @inheritDoc
     */
    public static function getEntityClassName(): string
    {
        return PersonController::getEntityClassName();
    }

    /**
     * @inheritDoc
     */
    public static function getEntitiesTag(): string
    {
        return PersonController::getEntitiesTag();
    }

    /**
     * @inheritDoc
     */
    public static function getEntityIdName(): string
    {
        return PersonController::getEntityIdName();
    }

    /**
     * Summary: GET /persons/{personId}/entities
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
     * PUT /persons/{personId}/entities/add/{stuffId}
     * PUT /persons/{personId}/entities/rem/{stuffId}
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
        return $this->operationStuff($request, $response, $args, $elementData);    }

    /**
     * Summary: GET /persons/{personId}/products
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function getProducts(Request $request, Response $response, array $args): Response
    {
        $elementData = [
            'getter' => 'getProducts',
            'stuff' => ProductController::getEntitiesTag(),
        ];
        return $this->getElements($response, $args, $elementData);    }

    /**
     * PUT /persons/{personId}/products/add/{stuffId}
     * PUT /persons/{personId}/products/rem/{stuffId}
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     * @throws ORMException
     */
    public function operationProduct(Request $request, Response $response, array $args): Response
    {
        $elementData = [
            'stuffEName' => ProductController::getEntityClassName(),
            'stuffId' => $args['stuffId'],
            'getter' => 'getProducts',
            'stuff' => ProductController::getEntitiesTag(),
        ];
        return $this->operationStuff($request, $response, $args, $elementData);
    }
}
