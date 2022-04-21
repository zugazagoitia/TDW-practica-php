<?php

/**
 * tests/Controller/Entity/EntityControllerTest.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\Test\ACiencia\Controller\Entity;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use TDW\ACiencia\Utility\Utils;
use TDW\Test\ACiencia\Controller\BaseTestCase;

/**
 * Class EntityControllerTest
 */
class EntityControllerTest extends BaseTestCase
{
    /** @var string Path para la gestión de entidades */
    protected const RUTA_API = '/api/v1/entities';

    /** @var array Admin data */
    protected static array $writer;

    /** @var array reader user data */
    protected static array $reader;

    /**
     * Se ejecuta una vez al inicio de las pruebas de la clase
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$writer = [
            'username' => getenv('ADMIN_USER_NAME'),
            'email'    => getenv('ADMIN_USER_EMAIL'),
            'password' => getenv('ADMIN_USER_PASSWD'),
        ];

        self::$reader = [
            'username' => self::$faker->userName(),
            'email'    => self::$faker->email(),
            'password' => self::$faker->password(),
        ];

        // load user admin fixtures
        self::$writer['id'] = Utils::loadUserData(
            self::$writer['username'],
            self::$writer['email'],
            self::$writer['password'],
            true
        );

        // load user reader fixtures
        self::$reader['id'] = Utils::loadUserData(
            self::$reader['username'],
            self::$reader['email'],
            self::$reader['password'],
            false
        );
    }

    /**
     * Test GET /entities 404 NOT FOUND
     */
    public function testCGetEntities404NotFound()
    {
        self::$writer['authHeader'] =
            $this->getTokenHeaders(self::$writer['username'], self::$writer['password']);
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '?order=id&ordering=ASC',
            null,
            self::$writer['authHeader']
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test POST /entities 201 CREATED
     *
     * @depends testCGetEntities404NotFound
     */
    public function testPostEntity201Created()
    {
        $p_data = [
            'name'      => self::$faker->words(3, true),
            'birthDate' => self::$faker->date(),
            'deathDate' => self::$faker->date(),
            'imageUrl'  => self::$faker->imageUrl(),
            'wikiUrl'   => self::$faker->url()
        ];
        $response = $this->runApp(
            'POST',
            self::RUTA_API,
            $p_data,
            self::$writer['authHeader']
        );

        self::assertSame(201, $response->getStatusCode());
        self::assertNotEmpty($response->getHeader('Location'));
        $r_body = $response->getBody()->getContents();
        self::assertJson($r_body);
        $responseEntity = json_decode($r_body, true);
        $entityData = $responseEntity['entity'];
        self::assertNotEquals(0, $entityData['id']);
        self::assertSame($p_data['name'], $entityData['name']);
        self::assertSame($p_data['birthDate'], $entityData['birthDate']);
        self::assertSame($p_data['deathDate'], $entityData['deathDate']);
        self::assertSame($p_data['imageUrl'], $entityData['imageUrl']);
        self::assertSame($p_data['wikiUrl'], $entityData['wikiUrl']);

        return $entityData;
    }

    /**
     * Test POST /entities 422 UNPROCESSABLE ENTITY
     *
     * @depends testCGetEntities404NotFound
     */
    public function testPostEntity422UnprocessableEntity(): void
    {
        $p_data = [
            // 'name'      => self::$faker->words(3, true),
            'birthDate' => self::$faker->date(),
            'deathDate' => self::$faker->date(),
            'imageUrl'  => self::$faker->imageUrl(),
            'wikiUrl'   => self::$faker->url()
        ];
        $response = $this->runApp(
            'POST',
            self::RUTA_API,
            $p_data,
            self::$writer['authHeader']
        );
        $this->internalTestError($response, StatusCode::STATUS_UNPROCESSABLE_ENTITY);
    }

    /**
     * Test POST /entities 400 BAD REQUEST
     *
     * @param array $entity entity returned by testPostEntity201Created()
     *
     * @depends testPostEntity201Created
     */
    public function testPostEntity400BadRequest(array $entity): void
    {
        // Mismo name
        $p_data = [
            'name' => $entity['name'],
        ];
        $response = $this->runApp(
            'POST',
            self::RUTA_API,
            $p_data,
            self::$writer['authHeader']
        );
        $this->internalTestError($response, StatusCode::STATUS_BAD_REQUEST);
    }

    /**
     * Test GET /entities 200 OK
     *
     * @depends testPostEntity201Created
     *
     * @return array ETag header
     */
    public function testCGetEntities200Ok(): array
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API,
            null,
            self::$writer['authHeader']
        );
        self::assertSame(200, $response->getStatusCode());
        self::assertNotEmpty($response->getHeader('ETag'));
        $r_body = $response->getBody()->getContents();
        self::assertJson($r_body);
        self::assertStringContainsString('entities', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertArrayHasKey('entities', $r_data);
        self::assertIsArray($r_data['entities']);

        return $response->getHeader('ETag');
    }

    /**
     * Test GET /entities 304 NOT MODIFIED
     *
     * @param array $etag returned by testCGetEntities200Ok
     *
     * @depends testCGetEntities200Ok
     */
    public function testCGetEntities304NotModified(array $etag): void
    {
        $headers = array_merge(
            self::$writer['authHeader'],
            [ 'If-None-Match' => $etag ]
        );
        $response = $this->runApp(
            'GET',
            self::RUTA_API,
            null,
            $headers
        );
        self::assertSame(StatusCode::STATUS_NOT_MODIFIED, $response->getStatusCode());
    }

    /**
     * Test GET /entities/{entityId} 200 OK
     *
     * @param array $entity entity returned by testPostEntity201Created()
     *
     * @depends testPostEntity201Created
     *
     * @return array ETag header
     */
    public function testGetEntity200Ok(array $entity): array
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . $entity['id'],
            null,
            self::$writer['authHeader']
        );
        self::assertSame(200, $response->getStatusCode());
        self::assertNotEmpty($response->getHeader('ETag'));
        $r_body = $response->getBody()->getContents();
        self::assertJson($r_body);
        $entity_aux = json_decode($r_body, true);
        self::assertSame($entity, $entity_aux['entity']);

        return $response->getHeader('ETag');
    }

    /**
     * Test GET /entities/{entityId} 304 NOT MODIFIED
     *
     * @param array $entity entity returned by testPostEntity201Created()
     * @param array $etag returned by testGetEntity200Ok
     *
     * @depends testPostEntity201Created
     * @depends testGetEntity200Ok
     * @return string Entity Tag
     */
    public function testGetEntity304NotModified(array $entity, array $etag): string
    {
        $headers = array_merge(
            self::$writer['authHeader'],
            [ 'If-None-Match' => $etag ]
        );
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . $entity['id'],
            null,
            $headers
        );
        self::assertSame(StatusCode::STATUS_NOT_MODIFIED, $response->getStatusCode());

        return $etag[0];
    }

    /**
     * Test GET /entities/entityname/{entityname} 204 NO CONTENT
     *
     * @param array $entity entity returned by testPostEntity201Created()
     *
     * @depends testPostEntity201Created
     */
    public function testGetEntityname204NoContent(array $entity): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/entityname/' . $entity['name']
        );
        self::assertSame(204, $response->getStatusCode());
        self::assertEmpty($response->getBody()->getContents());
    }

    /**
     * Test PUT /entities/{entityId}   209 UPDATED
     *
     * @param array $entity entity returned by testPostEntity201Created()
     * @param string $etag returned by testGetEntity304NotModified()
     *
     * @depends testPostEntity201Created
     * @depends testGetEntity304NotModified
     * @depends testPostEntity400BadRequest
     * @depends testCGetEntities304NotModified
     * @depends testGetEntityname204NoContent
     *
     * @return array modified entity data
     */
    public function testPutEntity209Updated(array $entity, string $etag): array
    {
        $p_data = [
            'name'  => self::$faker->words(3, true),
            'birthDate' => self::$faker->date(),
            'deathDate' => self::$faker->date(),
            'imageUrl'  => self::$faker->imageUrl(),
            'wikiUrl'   => self::$faker->url()
        ];

        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . $entity['id'],
            $p_data,
            array_merge(
                self::$writer['authHeader'],
                [ 'If-Match' => $etag ]
            )
        );
        self::assertSame(209, $response->getStatusCode());
        $r_body =  $response->getBody()->getContents();
        self::assertJson($r_body);
        $entity_aux = json_decode($r_body, true);
        self::assertSame($entity['id'], $entity_aux['entity']['id']);
        self::assertSame($p_data['name'], $entity_aux['entity']['name']);
        self::assertSame($p_data['birthDate'], $entity_aux['entity']['birthDate']);
        self::assertSame($p_data['deathDate'], $entity_aux['entity']['deathDate']);
        self::assertSame($p_data['imageUrl'], $entity_aux['entity']['imageUrl']);
        self::assertSame($p_data['wikiUrl'], $entity_aux['entity']['wikiUrl']);

        return $entity_aux['entity'];
    }

    /**
     * Test PUT /entities/{entityId} 400 BAD REQUEST
     *
     * @param array $entity entity returned by testPutEntity209Updated()
     *
     * @depends testPutEntity209Updated
     */
    public function testPutEntity400BadRequest(array $entity): void
    {
        $p_data = [ 'name' => self::$faker->words(3, true) ];
        $this->runApp(
            'POST',
            self::RUTA_API,
            $p_data,
            self::$writer['authHeader']
        );
        $r1 = $this->runApp( // Obtains etag header
            'HEAD',
            self::RUTA_API . '/' . $entity['id'],
            [],
            self::$writer['authHeader']
        );

        // entityname already exists
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . $entity['id'],
            $p_data,
            array_merge(
                self::$writer['authHeader'],
                [ 'If-Match' => $r1->getHeader('ETag') ]
            )
        );
        $this->internalTestError($response, StatusCode::STATUS_BAD_REQUEST);
    }

    /**
     * Test PUT /entity/{entityId} 412 PRECONDITION_FAILED
     *
     * @param array $entity entity returned by testPutEntity209Updated()
     *
     * @depends testPutEntity209Updated
     */
    public function testPutEntity412PreconditionFailed(array $entity): void
    {
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . $entity['id'],
            [],
            self::$writer['authHeader']
        );
        $this->internalTestError($response, StatusCode::STATUS_PRECONDITION_FAILED);
    }

    /**
     * Test OPTIONS /entities[/{entityId}] 204 NO CONTENT
     */
    public function testOptionsEntity204NoContent(): void
    {
        $response = $this->runApp(
            'OPTIONS',
            self::RUTA_API
        );
        self::assertSame(204, $response->getStatusCode());
        self::assertNotEmpty($response->getHeader('Allow'));
        self::assertEmpty($response->getBody()->getContents());

        $response = $this->runApp(
            'OPTIONS',
            self::RUTA_API . '/' . self::$faker->randomDigitNotNull()
        );
        self::assertSame(204, $response->getStatusCode());
        self::assertNotEmpty($response->getHeader('Allow'));
        self::assertEmpty($response->getBody()->getContents());
    }

    /**
     * Test DELETE /entities/{entityId} 204 NO CONTENT
     *
     * @param array $entity entity returned by testPostEntity201Created
     *
     * @depends testPostEntity201Created
     * @depends testPostEntity400BadRequest
     * @depends testPostEntity422UnprocessableEntity
     * @depends testPutEntity400BadRequest
     * @depends testPutEntity412PreconditionFailed
     * @depends testGetEntityname204NoContent
     *
     * @return int entityId
     */
    public function testDeleteEntity204NoContent(array $entity): int
    {
        $response = $this->runApp(
            'DELETE',
            self::RUTA_API . '/' . $entity['id'],
            null,
            self::$writer['authHeader']
        );
        self::assertSame(204, $response->getStatusCode());
        self::assertEmpty($response->getBody()->getContents());

        return $entity['id'];
    }

    /**
     * Test GET /entities/entityname/{entityname} 404 NOT FOUND
     *
     * @param array $entity entity returned by testPutEntity209Updated()
     *
     * @depends testPutEntity209Updated
     * @depends testDeleteEntity204NoContent
     */
    public function testGetEntityname404NotFound(array $entity): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/entityname/' . $entity['name']
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test GET    /entities/{entityId} 404 NOT FOUND
     * Test PUT    /entities/{entityId} 404 NOT FOUND
     * Test DELETE /entities/{entityId} 404 NOT FOUND
     *
     * @param int $entityId entity id. returned by testDeleteEntity204NoContent()
     * @param string $method
     * @return void
     * @dataProvider routeProvider404
     * @depends      testDeleteEntity204NoContent
     */
    public function testEntityStatus404NotFound(string $method, int $entityId): void
    {
        $response = $this->runApp(
            $method,
            self::RUTA_API . '/' . $entityId,
            null,
            self::$writer['authHeader']
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test GET    /entities 401 UNAUTHORIZED
     * Test POST   /entities 401 UNAUTHORIZED
     * Test GET    /entities/{entityId} 401 UNAUTHORIZED
     * Test PUT    /entities/{entityId} 401 UNAUTHORIZED
     * Test DELETE /entities/{entityId} 401 UNAUTHORIZED
     *
     * @param string $method
     * @param string $uri
     * @dataProvider routeProvider401()
     * @return void
     */
    public function testEntityStatus401Unauthorized(string $method, string $uri): void
    {
        $response = $this->runApp(
            $method,
            $uri
        );
        $this->internalTestError($response, StatusCode::STATUS_UNAUTHORIZED);
    }

    /**
     * Test POST   /entities 403 FORBIDDEN
     * Test PUT    /entities/{entityId} 403 FORBIDDEN => 404 NOT FOUND
     * Test DELETE /entities/{entityId} 403 FORBIDDEN => 404 NOT FOUND
     *
     * @param string $method
     * @param string $uri
     * @param int $statusCode
     * @return void
     * @dataProvider routeProvider403()
     */
    public function testEntitiestatus403Forbidden(string $method, string $uri, int $statusCode): void
    {
        self::$reader['authHeader'] = $this->getTokenHeaders(self::$reader['username'], self::$reader['password']);
        $response = $this->runApp(
            $method,
            $uri,
            null,
            self::$reader['authHeader']
        );
        $this->internalTestError($response, $statusCode);
    }

    // --------------
    // DATA PROVIDERS
    // --------------

    /**
     * Route provider (expected status: 401 UNAUTHORIZED)
     *
     * @return array [ method, url ]
     */
    public static function routeProvider401(): array
    {
        return [
            // 'cgetAction401'   => [ 'GET',    self::RUTA_API ],
            // 'getAction401'    => [ 'GET',    self::RUTA_API . '/1' ],
            'postAction401'   => [ 'POST',   self::RUTA_API ],
            'putAction401'    => [ 'PUT',    self::RUTA_API . '/1' ],
            'deleteAction401' => [ 'DELETE', self::RUTA_API . '/1' ],
        ];
    }

    /**
     * Route provider (expected status: 404 NOT FOUND)
     *
     * @return array [ method ]
     */
    public static function routeProvider404(): array
    {
        return [
            'getAction404'    => [ 'GET' ],
            'putAction404'    => [ 'PUT' ],
            'deleteAction404' => [ 'DELETE' ],
        ];
    }

    /**
     * Route provider (expected status: 403 FORBIDDEN (security) => 404 NOT FOUND)
     *
     * @return array [ method, url, statusCode ]
     */
    public static function routeProvider403(): array
    {
        return [
            'postAction403'   => [ 'POST',   self::RUTA_API, StatusCode::STATUS_FORBIDDEN ],
            'putAction403'    => [ 'PUT',    self::RUTA_API . '/1', StatusCode::STATUS_NOT_FOUND ],
            'deleteAction403' => [ 'DELETE', self::RUTA_API . '/1', StatusCode::STATUS_NOT_FOUND  ],
        ];
    }
}
