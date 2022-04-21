<?php

/**
 * tests/Controller/Person/PersonControllerTest.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\Test\ACiencia\Controller\Person;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use TDW\ACiencia\Utility\Utils;
use TDW\Test\ACiencia\Controller\BaseTestCase;

/**
 * Class PersonControllerTest
 */
class PersonControllerTest extends BaseTestCase
{
    /** @var string Path para la gestión de personas */
    protected const RUTA_API = '/api/v1/persons';

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
     * Test GET /persons 404 NOT FOUND
     */
    public function testCGetPersons404NotFound()
    {
        self::$writer['authHeader'] =
            $this->getTokenHeaders(self::$writer['username'], self::$writer['password']);
        $response = $this->runApp(
            'GET',
            self::RUTA_API,
            null,
            self::$writer['authHeader']
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test POST /persons 201 CREATED
     *
     * @depends testCGetPersons404NotFound
     */
    public function testPostPerson201Created()
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
        $responsePerson = json_decode($r_body, true);
        $personData = $responsePerson['person'];
        self::assertNotEquals(0, $personData['id']);
        self::assertSame($p_data['name'], $personData['name']);
        self::assertSame($p_data['birthDate'], $personData['birthDate']);
        self::assertSame($p_data['deathDate'], $personData['deathDate']);
        self::assertSame($p_data['imageUrl'], $personData['imageUrl']);
        self::assertSame($p_data['wikiUrl'], $personData['wikiUrl']);

        return $personData;
    }

    /**
     * Test POST /persons 422 UNPROCESSABLE ENTITY
     *
     * @depends testCGetPersons404NotFound
     */
    public function testPostPerson422UnprocessableEntity(): void
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
     * Test POST /persons 400 BAD REQUEST
     *
     * @param array $person person returned by testPostPerson201Created()
     *
     * @depends testPostPerson201Created
     */
    public function testPostPerson400BadRequest(array $person): void
    {
        // Mismo name
        $p_data = [
            'name' => $person['name'],
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
     * Test GET /persons 200 OK
     *
     * @depends testPostPerson201Created
     *
     * @return array ETag header
     */
    public function testCGetPersons200Ok(): array
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
        $r_data = json_decode($r_body, true);
        self::assertArrayHasKey('persons', $r_data);
        self::assertIsArray($r_data['persons']);

        return $response->getHeader('ETag');
    }

    /**
     * Test GET /persons 304 NOT MODIFIED
     *
     * @param array $etag returned by testCGetPersons200Ok
     *
     * @depends testCGetPersons200Ok
     */
    public function testCGetPersons304NotModified(array $etag): void
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
     * Test GET /persons/{personId} 200 OK
     *
     * @param array $person person returned by testPostPerson201Created()
     *
     * @depends testPostPerson201Created
     *
     * @return array ETag header
     */
    public function testGetPerson200Ok(array $person): array
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . $person['id'],
            null,
            self::$writer['authHeader']
        );
        self::assertSame(200, $response->getStatusCode());
        self::assertNotEmpty($response->getHeader('ETag'));
        $r_body = $response->getBody()->getContents();
        self::assertJson($r_body);
        $person_aux = json_decode($r_body, true);
        self::assertSame($person, $person_aux['person']);

        return $response->getHeader('ETag');
    }

    /**
     * Test GET /persons/{personId} 304 NOT MODIFIED
     *
     * @param array $person person returned by testPostPerson201Created()
     * @param array $etag returned by testGetPerson200Ok
     *
     * @depends testPostPerson201Created
     * @depends testGetPerson200Ok
     * @return string Entity Tag
     */
    public function testGetPerson304NotModified(array $person, array $etag): string
    {
        $headers = array_merge(
            self::$writer['authHeader'],
            [ 'If-None-Match' => $etag ]
        );
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . $person['id'],
            null,
            $headers
        );
        self::assertSame(StatusCode::STATUS_NOT_MODIFIED, $response->getStatusCode());

        return $etag[0];
    }

    /**
     * Test GET /persons/personname/{personname} 204 OK
     *
     * @param array $person person returned by testPostPerson201()
     *
     * @depends testPostPerson201Created
     */
    public function testGetPersonname204NoContent(array $person): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/personname/' . $person['name']
        );
        self::assertSame(204, $response->getStatusCode());
        self::assertEmpty($response->getBody()->getContents());
    }

    /**
     * Test PUT /persons/{personId}   209 UPDATED
     *
     * @param array $person person returned by testPostPerson201Created()
     * @param string $etag returned by testGetPerson304NotModified
     *
     * @depends testPostPerson201Created
     * @depends testGetPerson304NotModified
     * @depends testPostPerson400BadRequest
     * @depends testCGetPersons304NotModified
     * @depends testGetPersonname204NoContent
     *
     * @return array modified person data
     */
    public function testPutPerson209Updated(array $person, string $etag): array
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
            self::RUTA_API . '/' . $person['id'],
            $p_data,
            array_merge(
                self::$writer['authHeader'],
                [ 'If-Match' => $etag ]
            )
        );
        self::assertSame(209, $response->getStatusCode());
        $r_body = $response->getBody()->getContents();
        self::assertJson($r_body);
        $person_aux = json_decode($r_body, true);
        self::assertSame($person['id'], $person_aux['person']['id']);
        self::assertSame($p_data['name'], $person_aux['person']['name']);
        self::assertSame($p_data['birthDate'], $person_aux['person']['birthDate']);
        self::assertSame($p_data['deathDate'], $person_aux['person']['deathDate']);
        self::assertSame($p_data['imageUrl'], $person_aux['person']['imageUrl']);
        self::assertSame($p_data['wikiUrl'], $person_aux['person']['wikiUrl']);

        return $person_aux['person'];
    }

    /**
     * Test PUT /persons/{personId} 400 BAD REQUEST
     *
     * @param array $person person returned by testPutPerson209Updated()
     *
     * @depends testPutPerson209Updated
     */
    public function testPutPerson400BadRequest(array $person): void
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
            self::RUTA_API . '/' . $person['id'],
            [],
            self::$writer['authHeader']
        );

        // personname already exists
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . $person['id'],
            $p_data,
            array_merge(
                self::$writer['authHeader'],
                [ 'If-Match' => $r1->getHeader('ETag') ]
            )
        );
        $this->internalTestError($response, StatusCode::STATUS_BAD_REQUEST);
    }

    /**
     * Test PUT /person/{personId} 412 PRECONDITION_FAILED
     *
     * @param array $person person returned by testPutPerson209Updated()
     *
     * @depends testPutPerson209Updated
     */
    public function testPutPerson412PreconditionFailed(array $person): void
    {
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . $person['id'],
            [],
            self::$writer['authHeader']
        );
        $this->internalTestError($response, StatusCode::STATUS_PRECONDITION_FAILED);
    }

    /**
     * Test OPTIONS /persons[/{personId}] 204 NO CONTENT
     */
    public function testOptionsPerson204NoContent(): void
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
     * Test DELETE /persons/{personId} 204 NO CONTENT
     *
     * @param array $person person returned by testPostPerson201Created()
     *
     * @depends testPostPerson201Created
     * @depends testPostPerson400BadRequest
     * @depends testPostPerson422UnprocessableEntity
     * @depends testPutPerson400BadRequest
     * @depends testPutPerson412PreconditionFailed
     * @depends testGetPersonname204NoContent
     *
     * @return int personId
     */
    public function testDeletePerson204NoContent(array $person): int
    {
        $response = $this->runApp(
            'DELETE',
            self::RUTA_API . '/' . $person['id'],
            null,
            self::$writer['authHeader']
        );
        self::assertSame(204, $response->getStatusCode());
        self::assertEmpty($response->getBody()->getContents());

        return $person['id'];
    }

    /**
     * Test GET /persons/personname/{personname} 404 NOT FOUND
     *
     * @param array $person person returned by testPutPerson209Updated()
     *
     * @depends testPutPerson209Updated
     * @depends testDeletePerson204NoContent
     */
    public function testGetPersonname404NotFound(array $person): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/personname/' . $person['name']
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test GET    /persons/{personId} 404 NOT FOUND
     * Test PUT    /persons/{personId} 404 NOT FOUND
     * Test DELETE /persons/{personId} 404 NOT FOUND
     *
     * @param int $personId person id. returned by testDeletePerson204NoContent()
     * @param string $method
     * @return void
     * @dataProvider routeProvider404
     * @depends      testDeletePerson204NoContent
     */
    public function testPersonStatus404NotFound(string $method, int $personId): void
    {
        $response = $this->runApp(
            $method,
            self::RUTA_API . '/' . $personId,
            null,
            self::$writer['authHeader']
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test GET    /persons 401 UNAUTHORIZED
     * Test POST   /persons 401 UNAUTHORIZED
     * Test GET    /persons/{personId} 401 UNAUTHORIZED
     * Test PUT    /persons/{personId} 401 UNAUTHORIZED
     * Test DELETE /persons/{personId} 401 UNAUTHORIZED
     *
     * @param string $method
     * @param string $uri
     * @dataProvider routeProvider401()
     * @return void
     */
    public function testPersonStatus401Unauthorized(string $method, string $uri): void
    {
        $response = $this->runApp(
            $method,
            $uri
        );
        $this->internalTestError($response, StatusCode::STATUS_UNAUTHORIZED);
    }

    /**
     * Test POST   /persons 403 FORBIDDEN
     * Test PUT    /persons/{personId} 403 FORBIDDEN => 404 NOT FOUND
     * Test DELETE /persons/{personId} 403 FORBIDDEN => 404 NOT FOUND
     *
     * @param string $method
     * @param string $uri
     * @param int $statusCode
     * @return void
     * @dataProvider routeProvider403()
     */
    public function testPersonStatus403Forbidden(string $method, string $uri, int $statusCode): void
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
            'deleteAction403' => [ 'DELETE', self::RUTA_API . '/1', StatusCode::STATUS_NOT_FOUND ],
        ];
    }
}
