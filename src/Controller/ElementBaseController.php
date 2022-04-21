<?php

/**
 * src/Controller/ElementBaseController.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Controller;

use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;
use Slim\Routing\RouteContext;
use TDW\ACiencia\Entity\Element;
use TDW\ACiencia\Entity\Role;
use TDW\ACiencia\Utility\Error;

/**
 * Class ElementBaseController
 */
abstract class ElementBaseController
{
    protected EntityManager $entityManager;

    // constructor receives the EntityManager from container instance
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Tag name
     *
     * @return string
     */
    abstract public static function getEntitiesTag(): string;

    /**
     * Name of the controlled class
     *
     * @return string
     */
    abstract public static function getEntityClassName(): string;

    /**
     * Id name
     * @return string
     */
    abstract public static function getEntityIdName(): string;

    /**
     * Summary: Returns all elements
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     *
     * @todo add pagination
     * @todo add filtering
     */
    public function cget(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $criteria = new Criteria();
        if (array_key_exists('order', $params)) {
            $order = (in_array($params['order'], ['id', 'name'])) ? $params['order'] : null;
        }
        if (array_key_exists('ordering', $params)) {
            $ordering = ('DESC' === $params['ordering']) ? 'DESC' : null;
        }
        $criteria->orderBy([$order ?? 'id' => $ordering ?? 'ASC']);

        $elements = $this->entityManager
            ->getRepository(static::getEntityClassName())
            ->matching($criteria)
            ->getValues();

        if (0 === count($elements)) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        // Caching with ETag
        $etag = md5(json_encode($elements));
        if ($request->hasHeader('If-None-Match') && in_array($etag, $request->getHeader('If-None-Match'))) {
            return $response->withStatus(StatusCode::STATUS_NOT_MODIFIED); // 304
        }

        return $response
            ->withAddedHeader('ETag', $etag)
            ->withAddedHeader('Cache-Control', 'private')
            ->withJson([ static::getEntitiesTag() => $elements ]);
    }

    /**
     * Summary: Returns a element based on a single id
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function get(Request $request, Response $response, array $args): Response
    {
        $idName = static::getEntityIdName();
        $element = $this->entityManager->getRepository(static::getEntityClassName())
            ->find($args[$idName]);
        if (null === $element) {
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        // Caching with ETag
        $etag = md5(json_encode($element));
        if ($request->hasHeader('If-None-Match') && in_array($etag, $request->getHeader('If-None-Match'))) {
            return $response->withStatus(StatusCode::STATUS_NOT_MODIFIED); // 304
        }

        return $response
            ->withAddedHeader('ETag', $etag)
            ->withAddedHeader('Cache-Control', 'private')
            ->withJson($element);
    }

    /**
     * Summary: Returns status code 204 if $elementName exists
     *
     * @param Response $response
     * @param string $elementName
     * @return Response
     */
    public function getElementByName(Response $response, string $elementName): Response
    {
        $element = $this->entityManager
            ->getRepository(static::getEntityClassName())
            ->findOneBy([ 'name' => $elementName ]);

        return (null === $element)
            ? Error::error($response, StatusCode::STATUS_NOT_FOUND)     // 404
            : $response->withStatus(StatusCode::STATUS_NO_CONTENT);     // 204
    }

    /**
     * Summary: Deletes a element
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws ORMException|OptimisticLockException
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        if (!$this->checkWriterScope($request)) { // 403 => 404 por seguridad
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        $idName = static::getEntityIdName();
        $product = $this->entityManager->getRepository(static::getEntityClassName())->find($args[$idName]);

        if (null === $product) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return $response
            ->withStatus(StatusCode::STATUS_NO_CONTENT);  // 204
    }

    /**
     * Summary: Provides the list of HTTP supported methods
     *
     * @param  Request $request
     * @param  Response $response
     * @return Response
     */
    public function options(Request $request, Response $response): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $routingResults = $routeContext->getRoutingResults();
        $methods = $routingResults->getAllowedMethods();

        return $response
            ->withStatus(204)
            ->withAddedHeader('Cache-Control', 'private')
            ->withAddedHeader(
                'Allow',
                implode(', ', $methods)
            );
    }

    /**
     * Summary: Creates a new element
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ORMException
     */
    public function post(Request $request, Response $response): Response
    {
        if (!$this->checkWriterScope($request)) { // 403
            return Error::error($response, StatusCode::STATUS_FORBIDDEN);
        }

        $req_data = $request->getParsedBody() ?? json_decode($request->getBody(), true) ?? [];

        if (!isset($req_data['name'])) { // 422 - Faltan datos
            return Error::error($response, StatusCode::STATUS_UNPROCESSABLE_ENTITY);
        }

        // hay datos -> procesarlos
        $criteria = new Criteria();
        $criteria
            ->where($criteria::expr()->eq('name', $req_data['name']));
        // STATUS_BAD_REQUEST 400: element name already exists
        if ($this->entityManager->getRepository(static::getEntityClassName())->matching($criteria)->count()) {
            return Error::error($response, StatusCode::STATUS_BAD_REQUEST);
        }

        // 201
        $entityName = static::getEntityClassName();
        $element = new $entityName($req_data['name']);
        $this->updateElement($element, $req_data);
        $this->entityManager->persist($element);
        $this->entityManager->flush();

        return $response
            ->withAddedHeader(
                'Location',
                $request->getUri() . '/' . $element->getId()
            )
            ->withJson($element, StatusCode::STATUS_CREATED);
    }

    /**
     * Summary: Updates a element
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws ORMException
     */
    public function put(Request $request, Response $response, array $args): Response
    {
        if (!$this->checkWriterScope($request)) { // 403 => 404 por seguridad
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        $req_data
            = $request->getParsedBody()
            ?? json_decode($request->getBody(), true)
            ?? [];
        // recuperar el elemento
        $idName = static::getEntityIdName();
        /** @var Element|null $element */
        $element = $this->entityManager->getRepository(static::getEntityClassName())->find($args[$idName]);

        if (null === $element) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        // Optimistic Locking (strong validation)
        $etag = md5(json_encode($element));
        if (!$request->hasHeader('If-Match') || !in_array($etag, $request->getHeader('If-Match'))) {
            return Error::error($response, StatusCode::STATUS_PRECONDITION_FAILED); // 412
        }

        if (isset($req_data['name'])) { // 400
            $elementId = $this->findIdBy(static::getEntityClassName(), 'name', $req_data['name']);
            if ($elementId && (intval($args[$idName]) !== $elementId)) {
                // 400 BAD_REQUEST: elementname already exists
                return Error::error($response, StatusCode::STATUS_BAD_REQUEST);
            }
            $element->setName($req_data['name']);
        }

        $this->updateElement($element, $req_data);
        $this->entityManager->flush();

        return $response
            ->withStatus(209, 'Content Returned')
            ->withJson($element);
    }

    /**
     * Determines if a value exists for an attribute
     *
     * @param string $entityName
     * @param string $attr attribute
     * @param string $value value
     * @return int
     */
    protected function findIdBy(string $entityName, string $attr, string $value): int
    {
        $element = $this->entityManager->getRepository($entityName)->findOneBy([ $attr => $value ]);
        return $element?->getId() ?? 0;
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function checkWriterScope(Request $request): bool
    {
        $scopes = $request->getAttribute('token')->claims()->get('scopes');
        return in_array(Role::ROLE_WRITER, $scopes, true);
    }

    /**
     * Update $element with $data attributes
     *
     * @param Element $element
     * @param array $data
     */
    protected function updateElement(Element $element, array $data): void
    {
        foreach ($data as $attr => $datum) {
            switch ($attr) {
                case 'birthDate':
                    ($date = DateTime::createFromFormat('!Y-m-d', $datum)) ? $element->setBirthDate($date) : null;
                    break;
                case 'deathDate':
                    ($date = DateTime::createFromFormat('!Y-m-d', $datum)) ? $element->setDeathDate($date) : null;
                    break;
                case 'imageUrl':
                    $element->setImageUrl($datum);
                    break;
                case 'wikiUrl':
                    $element->setWikiUrl($datum);
                    break;
            }
        }
    }
}
