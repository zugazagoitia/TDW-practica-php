<?php

/**
 * tests/Controller/BaseTestCase.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\Test\ACiencia\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;
use Slim\Http\Factory\DecoratedServerRequestFactory;
use Slim\Psr7\Environment;
use Slim\Psr7\Factory\ServerRequestFactory;
use TDW\ACiencia\Utility\Error;
use TDW\ACiencia\Utility\Utils;
use Throwable;

/**
 * This is an example class that shows how you could set up a method that
 * runs the application. Note that it doesn't cover all use-cases and is
 * tuned to the specifics of this skeleton app, so if your needs are
 * different, you'll need to change it.
 */
class BaseTestCase extends TestCase
{
    /** @var array $writer Admin User */
    protected static array $writer = [];

    protected static \Faker\Generator $faker;

    public static function getFaker(): \Faker\Generator
    {
        if (!isset(self::$faker)) {
            self::$faker = \Faker\Factory::create('es_ES');
        }
        return self::$faker;
    }

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        self::$faker = self::getFaker();
        Utils::updateSchema();
    }

    /**
     * Process the application given a request method and URI
     *
     * @param string $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string $requestUri the request URI
     * @param array|null $requestData the request data
     * @param array|null $requestHeaders the request headers
     *
     * @return Response
     */
    public function runApp(
        string $requestMethod,
        string $requestUri,
        array $requestData = null,
        array $requestHeaders = null
    ): Response {

        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD'     => $requestMethod,
                'REQUEST_URI'        => $requestUri,
                'HTTP_AUTHORIZATION' => $requestHeaders['Authorization'] ?? null,
            ]
        );

        // Set up a request object based on the environment
        $factory = new DecoratedServerRequestFactory(new ServerRequestFactory());
        $request = $factory->createServerRequest(
            $requestMethod,
            $requestUri,
            $environment
        );

        // Add request data, if it exists
        if (null !== $requestData) {
            $request = $request->withParsedBody($requestData);
        }

        // Add request headers, if it exists
        if (null !== $requestHeaders) {
            foreach ($requestHeaders as $header_name => $value) {
                $request = clone $request->withAddedHeader($header_name, $value);
            }
        }

        // Instantiate the application
        /** @var App $app */
        $app = (require __DIR__ . '/../../config/bootstrap.php');

        // Process the application
        try {
            $response = $app->handle($request);
        } catch (Throwable $exception) {
            die('ERROR: ' . $exception->getMessage());
        }

        // Return the response
        return $response;
    }

    /**
     * Obtiene la cabecera Authorization a través de la ruta correspondiente
     *
     * @param string|null $username user name
     * @param string|null $password user password
     *
     * @return array cabeceras con el token obtenido
     */
    protected function getTokenHeaders(
        ?string $username = null,
        ?string $password = null
    ): array {
        $data = [
            'username' => $username,
            'password' => $password,
        ];
        $response = $this->runApp(
            'POST',
            $_ENV['RUTA_LOGIN'],
            $data
        );
        return [ 'Authorization' => $response->getHeaderLine('Authorization') ];
    }

    /**
     * Test error messages
     *
     * @param Response $response
     * @param int $errorCode
     */
    protected function internalTestError(Response $response, int $errorCode): void
    {
        self::assertSame($errorCode, $response->getStatusCode());
        $r_body = $response->getBody()->getContents();
        self::assertJson($r_body);
        try {
            $r_data = json_decode($r_body, true, 512, JSON_THROW_ON_ERROR);
            self::assertArrayHasKey('code', $r_data);
            self::assertArrayHasKey('message', $r_data);
            self::assertSame($errorCode, $r_data['code']);
            self::assertSame(Error::MESSAGES[$errorCode], $r_data['message']);
        } catch (Throwable $exception) {
            die('ERROR: ' . $exception->getMessage());
        }
    }
}
