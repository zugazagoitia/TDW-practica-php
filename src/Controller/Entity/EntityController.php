<?php

/**
 * src/Controller/Entity/EntityController.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Controller\Entity;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;
use TDW\ACiencia\Controller\ElementBaseController;
use TDW\ACiencia\Entity\Entity;

/**
 * Class EntityController
 */
class EntityController extends ElementBaseController
{
    /** @var string ruta api gestión entityas  */
    public const PATH_ENTITIES = '/entities';

    /**
     * @inheritDoc
     */
    public static function getEntitiesTag(): string
    {
        return 'entities';
    }

    /**
     * @inheritDoc
     */
    public static function getEntityClassName(): string
    {
        return Entity::class;
    }

    /**
     * @inheritDoc
     */
    public static function getEntityIdName(): string
    {
        return 'entityId';
    }

    /**
     * Summary: Returns status code 204 if entityname exists
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function getEntityname(Request $request, Response $response, array $args): Response
    {
        return $this->getElementByName($response, $args['entityname']);
    }
}
