<?php

/**
 * src/Controller/LoginController.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Controller;

use Doctrine\ORM\EntityManager;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;
use Slim\Routing\RouteContext;
use TDW\ACiencia\Auth\JwtAuth;
use TDW\ACiencia\Entity\Role;
use TDW\ACiencia\Entity\User;
use TDW\ACiencia\Utility\Error;

/**
 * Class CuestionController
 */
class LoginController
{
    protected EntityManager $entityManager;
    protected JwtAuth $jwtAuth;

    // constructor receives container instance
    public function __construct(EntityManager $entityManager, JwtAuth $jwtAuth)
    {
        $this->entityManager = $entityManager;
        $this->jwtAuth = $jwtAuth;
    }

    /**
     * POST /access_token
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function post(Request $request, Response $response): Response
    {
        $req_data
            = $request->getParsedBody()
            ?? json_decode($request->getBody(), true, 3, JSON_INVALID_UTF8_IGNORE);

        /** @var User $user */
        $user = null;
        if (isset($req_data['username'], $req_data['password'])) {
            $user = $this->entityManager
                ->getRepository(User::class)
                ->findOneBy([ 'username' => $req_data['username'] ]);
        }

        if (!$user?->validatePassword($req_data['password'])) {    // 404 instead of 401
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        if(!$user->isActive()) { // 403 - User not active
            return Error::error($response, StatusCode::STATUS_FORBIDDEN);
        }

        if (!array_key_exists('scope', $req_data)) {
            $token = $this->jwtAuth->createJwt($user)->toString();
        } else {
            $claimedScopes = preg_split(
                '/ |(\+)/',
                $req_data['scope'],
                -1,
                PREG_SPLIT_NO_EMPTY
            );
            $claimedScopes = empty($claimedScopes[0]) ? Role::ROLES : $claimedScopes;
            $token = $this->jwtAuth->createJwt($user, $claimedScopes)->toString();
        }

        return $response
            ->withJson([
                'token_type' => 'Bearer',
                'expires_in' => $this->jwtAuth->getLifetime(),    // 14400
                'access_token' => $token
            ])
            ->withHeader('Cache-Control', 'no-store')   // Prevención de almacenamiento en caché
            ->withHeader('Authorization', 'Bearer ' . $token);
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
}
