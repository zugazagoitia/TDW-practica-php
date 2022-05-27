<?php

/**
 * src/Controller/Person/PersonController.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Controller\Person;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;
use TDW\ACiencia\Controller\ElementBaseController;
use TDW\ACiencia\Entity\Person;

/**
 * Class PersonController
 */
class PersonController extends ElementBaseController
{
    /** @var string ruta api gestión personas  */
    public const PATH_PERSONS = '/persons';

    /**
     * @inheritDoc
     */
    public static function getEntitiesTag(): string
    {
        return 'persons';
    }

    /**
     * @inheritDoc
     */
    public static function getEntityClassName(): string
    {
        return Person::class;
    }

    /**
     * @inheritDoc
     */
    public static function getEntityIdName(): string
    {
        return 'personId';
    }

    /**
     * Summary: Returns status code 204 if personname exists
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function getPersonname(Request $request, Response $response, array $args): Response
    {
        return $this->getElementByName($response, $args['personname']);
    }
}
