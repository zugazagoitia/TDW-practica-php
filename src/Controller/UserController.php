<?php

/**
 * src/Controller/UserController.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;
use Slim\Routing\RouteContext;
use TDW\ACiencia\Entity\Role;
use TDW\ACiencia\Entity\User;
use TDW\ACiencia\Utility\Error;
use Throwable;

/**
 * Class UserController
 */
class UserController
{
    /** @var string ruta api gestión usuarios  */
    public const PATH_USERS = '/users';

    protected EntityManager $entityManager;

    // constructor receives container instance
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Summary: Returns all users
     *
     * @param Request $request
     * @param Response $response
     *
     * @return Response
     */
    public function cget(Request $request, Response $response): Response
    {
        $users = $this->entityManager
            ->getRepository(User::class)
            ->findAll();

        // @codeCoverageIgnoreStart
        if (0 === count($users)) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }
        // @codeCoverageIgnoreEnd

        // Caching with ETag
        $etag = md5(json_encode($users));
        if ($request->hasHeader('If-None-Match') && in_array($etag, $request->getHeader('If-None-Match'))) {
                return $response->withStatus(StatusCode::STATUS_NOT_MODIFIED); // 304
        }

        return $response
            ->withAddedHeader('ETag', $etag)
            ->withAddedHeader('Cache-Control', 'private')
            ->withJson([ 'users' => $users ]);
    }

    /**
     * Summary: Returns a user based on a single userId
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function get(Request $request, Response $response, array $args): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($args['userId']);
        if (null === $user) {
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        // Caching with ETag
        $etag = md5(json_encode($user));
        if ($request->hasHeader('If-None-Match') && in_array($etag, $request->getHeader('If-None-Match'))) {
            return $response->withStatus(StatusCode::STATUS_NOT_MODIFIED); // 304
        }

        return $response
            ->withAddedHeader('ETag', $etag)
            ->withAddedHeader('Cache-Control', 'private')
            ->withJson($user);
    }

    /**
     * Summary: Returns status code 204 if username exists
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function getUsername(Request $request, Response $response, array $args): Response
    {
        $usuario = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy([ 'username' => $args['username'] ]);

        return (null === $usuario)
            ? Error::error($response, StatusCode::STATUS_NOT_FOUND)     // 404
            : $response->withStatus(StatusCode::STATUS_NO_CONTENT);     // 204
    }

    /**
     * Summary: Deletes a user
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     * @throws ORMException
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        if (!$this->checkWriterScope($request)) { // 403 => 404 por seguridad
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        $user = $this->entityManager->getRepository(User::class)->find($args['userId']);

        if (null === $user) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $response
            ->withStatus(StatusCode::STATUS_NO_CONTENT);  // 204
    }

    /**
     * Summary: Provides the list of HTTP supported methods
     *
     * @param  Request $request
     * @param  Response $response
     *
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
     * Summary: Creates a new user
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     *
     * @throws ORMException
     */
    public function post(Request $request, Response $response): Response
    {
        if (!$this->checkWriterScope($request)) { // 403
            return Error::error($response, StatusCode::STATUS_FORBIDDEN);
        }

        $req_data
            = $request->getParsedBody() ?? json_decode($request->getBody(), true) ?? [];

        if (!isset($req_data['username'], $req_data['email'], $req_data['password'])) { // 422 - Faltan datos
            return Error::error($response, StatusCode::STATUS_UNPROCESSABLE_ENTITY);
        }

        // hay datos -> procesarlos
        $criteria = new Criteria();
        $criteria
            ->where($criteria::expr()->eq('username', $req_data['username']))
            ->orWhere($criteria::expr()->eq('email', $req_data['email']));
        // STATUS_BAD_REQUEST 400: username or e-mail already exists
        if ($this->entityManager->getRepository(User::class)->matching($criteria)->count()) {
            return Error::error($response, StatusCode::STATUS_BAD_REQUEST);
        }

        // 201
        try {
            $user = new User(
                $req_data['username'],
                $req_data['email'],
                $req_data['password'],
                $req_data['role'] ?? Role::ROLE_READER
            );
        } catch (Throwable) {    // 400 BAD REQUEST: Unexpected role
            return Error::error($response, StatusCode::STATUS_BAD_REQUEST);
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $response
            ->withAddedHeader(
                'Location',
                $request->getUri() . '/' . $user->getId()
            )
            ->withJson($user, StatusCode::STATUS_CREATED);
    }

    /**
     * Summary: Updates a user
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     * @throws ORMException
     */
    public function put(Request $request, Response $response, array $args): Response
    {
        if (!$this->checkWriterScope($request)) { // 403 => 404 por seguridad
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        $req_data
            = $request->getParsedBody() ?? json_decode($request->getBody(), true) ?? [];
        /** @var User|null $user */
        $user = $this->entityManager->getRepository(User::class)->find($args['userId']);

        if (null === $user) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        // Optimistic Locking (strong validation)
        $etag = md5(json_encode($user));
        if (!$request->hasHeader('If-Match') || !in_array($etag, $request->getHeader('If-Match'))) {
            return Error::error($response, StatusCode::STATUS_PRECONDITION_FAILED); // 412
        }

        if (isset($req_data['username'])) {
            $usuarioId = $this->findIdBy('username', $req_data['username']);
            if ($usuarioId && intval($args['userId']) !== $usuarioId) {
                // 400 BAD_REQUEST: username already exists
                return Error::error($response, StatusCode::STATUS_BAD_REQUEST);
            }
            $user->setUsername($req_data['username']);
        }

        if (isset($req_data['email'])) {
            $usuarioId = $this->findIdBy('email', $req_data['email']);
            if ($usuarioId && intval($args['userId']) !== $usuarioId) {
                // 400 BAD_REQUEST: e-mail already exists
                return Error::error($response, StatusCode::STATUS_BAD_REQUEST);
            }
            $user->setEmail($req_data['email']);
        }

        // password
        if (isset($req_data['password'])) {
            $user->setPassword($req_data['password']);
        }

        // role
        if (isset($req_data['role'])) {
            try {
                $user->setRole($req_data['role']);
            } catch (Throwable) {    // 400 BAD_REQUEST: unexpected role
                return Error::error($response, StatusCode::STATUS_BAD_REQUEST);
            }
        }

        $this->entityManager->flush();

        return $response
            ->withStatus(209, 'Content Returned')
            ->withJson($user);
    }

    /**
     * Determines if a value exists for an attribute
     *
     * @param string $attr attribute
     * @param string $value value
     *
     * @return int
     */
    private function findIdBy(string $attr, string $value): int
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([ $attr => $value ]);
        return $user?->getId() ?? 0;
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function checkWriterScope(Request $request): bool
    {
        $scopes = $request->getAttribute('token')->claims()->get('scopes', null);
        return in_array(Role::ROLE_WRITER, $scopes, true);
    }
}
