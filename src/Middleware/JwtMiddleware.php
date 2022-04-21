<?php

/**
 * src/Middleware/JwtMiddleware.php
 *
 * @license ttps://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 *
 * @link    https://odan.github.io/2019/12/02/slim4-oauth2-jwt.html
 */

namespace TDW\ACiencia\Middleware;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use InvalidArgumentException;
use Lcobucci\JWT\Token\Plain;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TDW\ACiencia\Auth\JwtAuth;
use TDW\ACiencia\Utility\Error;

/**
 * Jwt Middleware
 */
final class JwtMiddleware implements MiddlewareInterface
{
    private JwtAuth $jwtAuth;

    private ResponseFactoryInterface $responseFactory;

    public function __construct(ContainerInterface $container)
    {
        $this->jwtAuth = $container->get(JwtAuth::class);
        $this->responseFactory = $container->get(ResponseFactoryInterface::class);
    }

    /**
     * Invoke middleware.
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorization = explode(' ', $request->getHeaderLine('Authorization'));
        $token = $authorization[1] ?? '';

        try {
            if (!$token || !$this->jwtAuth->validateToken($token)) {
                throw new InvalidArgumentException('Invalid token provided');
            }
        } catch (InvalidArgumentException) {
            return Error::error(
                $this->responseFactory->createResponse(),
                StatusCode::STATUS_UNAUTHORIZED
            );
        }

        // Append valid token
        /** @var Plain $parsedToken */
        $parsedToken = $this->jwtAuth->createParsedToken($token);

        return $handler->handle(
            $request
                ->withAttribute('token', $parsedToken)
                ->withAttribute('uid', $parsedToken->claims()->get('uid'))
        );
    }
}
