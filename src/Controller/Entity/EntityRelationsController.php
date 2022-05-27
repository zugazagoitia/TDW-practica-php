<?php

/**
 * src/Controller/Entity/EntityRelationsController.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Controller\Entity;

use Doctrine\ORM\ORMException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;
use TDW\ACiencia\Controller\ElementRelationsBaseController;
use TDW\ACiencia\Controller\Person\PersonController;
use TDW\ACiencia\Controller\Product\ProductController;

/**
 * Class EntityRelationsController
 */
final class EntityRelationsController extends ElementRelationsBaseController
{
    /**
     * @inheritDoc
     */
    public static function getEntityClassName(): string
    {
        return EntityController::getEntityClassName();
    }

    /**
     * @inheritDoc
     */
    public static function getEntitiesTag(): string
    {
        return EntityController::getEntitiesTag();
    }

    /**
     * @inheritDoc
     */
    public static function getEntityIdName(): string
    {
        return EntityController::getEntityIdName();
    }

    /**
     * Summary: GET /entities/{entityId}/persons
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
     * PUT /entities/{entityId}/persons/add/{stuffId}
     * PUT /entities/{entityId}/persons/rem/{stuffId}
     *
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

    /**
     * Summary: GET /entities/{entityId}/products
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
     * PUT /entities/{entityId}/products/add/{stuffId}
     * PUT /entities/{entityId}/products/rem/{stuffId}
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
