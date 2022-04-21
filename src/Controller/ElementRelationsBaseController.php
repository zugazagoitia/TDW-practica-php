<?php

/**
 * src/Controller/ElementRelationsBaseController.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Controller;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;
use TDW\ACiencia\Entity\Element;
use TDW\ACiencia\Utility\Error;

/**
 * Class ElementBaseController
 */
abstract class ElementRelationsBaseController extends ElementBaseController
{
    /**
     * Summary: get a list of related items
     * e.g.: GET /products/{productId}/entities
     *
     * @param Response $response
     * @param array $args
     * @param array $elementData
     *  - 'getter' => (string) getter function (e.g. 'getEntities')
     *  - 'stuff' => (string) items tag (e.g. 'entities')
     * @return Response
     */
    public function getElements(Response $response, array $args, array $elementData): Response
    {
        /** @var Element|null $element */
        $element = $this->entityManager
            ->getRepository(static::getEntityClassName())
            ->find($args[static::getEntityIdName()]);

        if (null === $element) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        return $response
            ->withAddedHeader('ETag', md5(json_encode($element)))
            ->withAddedHeader('Cache-Control', 'private')
            ->withJson([$elementData['stuff'] => $element->{$elementData['getter']}()]);
    }

    /**
     * Add and remove relationships between elements
     * e.g.: PUT /products/{productId}/entities/add/{stuffId}
     * e.g.: PUT /products/{productId}/entities/rem/{stuffId}
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @param array $elementData
     *  - 'stuffEName' => (string) Inversed class name (e.g. Entity::class)
     *  - 'stuffId' => (int) inversed element Id
     *  - 'getter' => (string) getter function (e.g. 'getEntities')
     *  - 'stuff' => (string) items tag (e.g. 'entities')
     * @return Response
     * @throws ORMException|OptimisticLockException
     */
    public function operationStuff(Request $request, Response $response, array $args, array $elementData): Response
    {
        if (!$this->checkWriterScope($request)) { // 403
            return Error::error($response, StatusCode::STATUS_FORBIDDEN);
        }

        /** @var Element|null $element */
        $element = $this->entityManager
            ->getRepository(static::getEntityClassName())
            ->find($args[static::getEntityIdName()]);

        if (null === $element) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        $stuff = $this->entityManager
            ->getRepository($elementData['stuffEName'])->find($elementData['stuffId']);
        if (null === $stuff) {     // 406
            return Error::error($response, StatusCode::STATUS_NOT_ACCEPTABLE);
        }

        $endPoint = $request->getUri()->getPath();
        $segments = explode('/', $endPoint);

        $operationAdd = sprintf('add%s', $this->className($elementData['stuffEName']));
        $operationRem = sprintf('remove%s', $this->className($elementData['stuffEName']));
        ('add' === $segments[array_key_last($segments) - 1])
            ? $element->{$operationAdd}($stuff)
            : $element->{$operationRem}($stuff);
        $this->entityManager->flush();

        return $response
            ->withStatus(209, 'Content Returned')
            ->withJson($element);
    }

    /**
     * @param string $fqcn  Fully Qualified Class Name
     *
     * @return string Class Name
     */
    private function className(string $fqcn): string
    {
        $elements = explode('\\', $fqcn);
        return $elements[array_key_last($elements)];
    }
}
