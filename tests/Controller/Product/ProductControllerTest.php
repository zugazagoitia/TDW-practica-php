<?php

/**
 * tests/Controller/Product/ProductControllerTest.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\Test\ACiencia\Controller\Product;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use TDW\ACiencia\Utility\Utils;
use TDW\Test\ACiencia\Controller\BaseTestCase;

/**
 * Class ProductControllerTest
 */
class ProductControllerTest extends BaseTestCase
{
    /** @var string Path para la gestión de productos */
    protected const RUTA_API = '/api/v1/products';

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
     * Test GET /products 404 NOT FOUND
     */
    public function testCGetProducts404NotFound()
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
     * Test POST /products 201 CREATED
     *
     * @depends testCGetProducts404NotFound
     */
    public function testPostProduct201Created()
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
        $responseProduct = json_decode($r_body, true);
        self::assertArrayHasKey('product', $responseProduct);
        $productData = $responseProduct['product'];
        self::assertNotEquals(0, $productData['id']);
        self::assertSame($p_data['name'], $productData['name']);
        self::assertSame($p_data['birthDate'], $productData['birthDate']);
        self::assertSame($p_data['deathDate'], $productData['deathDate']);
        self::assertSame($p_data['imageUrl'], $productData['imageUrl']);
        self::assertSame($p_data['wikiUrl'], $productData['wikiUrl']);

        return $productData;
    }

    /**
     * Test POST /users 422 UNPROCESSABLE ENTITY
     *
     * @depends testCGetProducts404NotFound
     */
    public function testPostProduct422UnprocessableEntity(): void
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
     * Test POST /products 400 BAD REQUEST
     *
     * @param array $product product returned by testPostProduct201Created()
     *
     * @depends testPostProduct201Created
     */
    public function testPostProduct400BadRequest(array $product): void
    {
        // Mismo username
        $p_data = [
            'name' => $product['name'],
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
     * Test GET /products 200 OK
     *
     * @depends testPostProduct201Created
     *
     * @return array ETag header
     */
    public function testCGetProducts200Ok(): array
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
        self::assertArrayHasKey('products', $r_data);
        self::assertIsArray($r_data['products']);

        return $response->getHeader('ETag');
    }

    /**
     * Test GET /products 304 NOT MODIFIED
     *
     * @param array $etag returned by testCGetproducts200Ok
     *
     * @depends testCGetProducts200Ok
     */
    public function testCGetProducts304NotModified(array $etag): void
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
     * Test GET /products/{productId} 200 OK
     *
     * @param array $product product returned by testPostProduct201Created()
     *
     * @depends testPostProduct201Created
     *
     * @return array ETag header
     */
    public function testGetProduct200Ok(array $product): array
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . $product['id'],
            null,
            self::$writer['authHeader']
        );
        self::assertSame(200, $response->getStatusCode());
        self::assertNotEmpty($response->getHeader('ETag'));
        $r_body = $response->getBody()->getContents();
        self::assertJson($r_body);
        $product_aux = json_decode($r_body, true);
        self::assertSame($product, $product_aux['product']);

        return $response->getHeader('ETag');
    }

    /**
     * Test GET /products/{productId} 304 NOT MODIFIED
     *
     * @param array $product product returned by testPostproduct201Created()
     * @param array $etag returned by testGetproduct200Ok
     *
     * @depends testPostProduct201Created
     * @depends testGetProduct200Ok
     * @return string Entity Tag
     */
    public function testGetProduct304NotModified(array $product, array $etag): string
    {
        $headers = array_merge(
            self::$writer['authHeader'],
            [ 'If-None-Match' => $etag ]
        );
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . $product['id'],
            null,
            $headers
        );
        self::assertSame(StatusCode::STATUS_NOT_MODIFIED, $response->getStatusCode());

        return $etag[0];
    }

    /**
     * Test GET /products/productname/{productname} 204 NO CONTENT
     *
     * @param array $product product returned by testPostProduct201()
     *
     * @depends testPostProduct201Created
     */
    public function testGetProductname204NoContent(array $product): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/productname/' . $product['name']
        );
        self::assertSame(204, $response->getStatusCode());
        self::assertEmpty($response->getBody()->getContents());
    }

    /**
     * Test PUT /products/{productId}   209 UPDATED
     *
     * @param array $product product returned by testPostProduct201Created()
     * @param string $etag returned by testGetProduct304NotModified
     *
     * @depends testPostProduct201Created
     * @depends testGetProduct304NotModified
     * @depends testPostProduct400BadRequest
     * @depends testCGetProducts304NotModified
     * @depends testGetProductname204NoContent
     *
     * @return array modified product data
     */
    public function testPutProduct209Updated(array $product, string $etag): array
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
            self::RUTA_API . '/' . $product['id'],
            $p_data,
            array_merge(
                self::$writer['authHeader'],
                [ 'If-Match' => $etag ]
            )
        );
        self::assertSame(209, $response->getStatusCode());
        $r_body = $response->getBody()->getContents();
        self::assertJson($r_body);
        $product_aux = json_decode($r_body, true);
        self::assertArrayHasKey('product', $product_aux);
        self::assertSame($product['id'], $product_aux['product']['id']);
        self::assertSame($p_data['name'], $product_aux['product']['name']);
        self::assertSame($p_data['birthDate'], $product_aux['product']['birthDate']);
        self::assertSame($p_data['deathDate'], $product_aux['product']['deathDate']);
        self::assertSame($p_data['imageUrl'], $product_aux['product']['imageUrl']);
        self::assertSame($p_data['wikiUrl'], $product_aux['product']['wikiUrl']);

        return $product_aux['product'];
    }

    /**
     * Test PUT /products/{productId} 400 BAD REQUEST
     *
     * @param array $product product returned by testPutProduct209Updated()
     *
     * @depends testPutProduct209Updated
     */
    public function testPutProduct400BadRequest(array $product): void
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
            self::RUTA_API . '/' . $product['id'],
            [],
            self::$writer['authHeader']
        );

        // productname already exists
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . $product['id'],
            $p_data,
            array_merge(
                self::$writer['authHeader'],
                [ 'If-Match' => $r1->getHeader('ETag') ]
            )
        );
        $this->internalTestError($response, StatusCode::STATUS_BAD_REQUEST);
    }

    /**
     * Test PUT /product/{productId} 412 PRECONDITION_FAILED
     *
     * @param array $product product returned by testPutProduct209Updated()
     *
     * @depends testPutProduct209Updated
     */
    public function testPutProduct412PreconditionFailed(array $product): void
    {
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . $product['id'],
            [],
            self::$writer['authHeader']
        );
        $this->internalTestError($response, StatusCode::STATUS_PRECONDITION_FAILED);
    }

    /**
     * Test OPTIONS /products[/{productId}] NO CONTENT
     */
    public function testOptionsProduct204NoContent(): void
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
     * Test DELETE /products/{productId} 204 NO CONTENT
     *
     * @param array $product product returned by testPostProduct201Created()
     *
     * @depends testPostProduct201Created
     * @depends testPostProduct400BadRequest
     * @depends testPostProduct422UnprocessableEntity
     * @depends testPutProduct400BadRequest
     * @depends testPutProduct412PreconditionFailed
     * @depends testGetProductname204NoContent
     *
     * @return int productId
     */
    public function testDeleteProduct204NoContent(array $product): int
    {
        $response = $this->runApp(
            'DELETE',
            self::RUTA_API . '/' . $product['id'],
            null,
            self::$writer['authHeader']
        );
        self::assertSame(204, $response->getStatusCode());
        self::assertEmpty($response->getBody()->getContents());

        return $product['id'];
    }

    /**
     * Test GET /products/productname/{productname} 404 NOT FOUND
     *
     * @param array $product product returned by testPutProduct209Updated()
     *
     * @depends testPutProduct209Updated
     * @depends testDeleteProduct204NoContent
     */
    public function testGetProductname404NotFound(array $product): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/productname/' . $product['name']
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test GET    /products/{productId} 404 NOT FOUND
     * Test PUT    /products/{productId} 404 NOT FOUND
     * Test DELETE /products/{productId} 404 NOT FOUND
     *
     * @param int $productId product id. returned by testDeleteProduct204NoContent()
     * @param string $method
     * @return void
     * @dataProvider routeProvider404
     * @depends      testDeleteProduct204NoContent
     */
    public function testProductStatus404NotFound(string $method, int $productId): void
    {
        $response = $this->runApp(
            $method,
            self::RUTA_API . '/' . $productId,
            null,
            self::$writer['authHeader']
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test GET    /products 401 UNAUTHORIZED
     * Test POST   /products 401 UNAUTHORIZED
     * Test PUT    /products/{productId} 401 UNAUTHORIZED
     * Test DELETE /products/{productId} 401 UNAUTHORIZED
     *
     * @param string $method
     * @param string $uri
     * @dataProvider routeProvider401()
     * @return void
     */
    public function testProductStatus401Unauthorized(string $method, string $uri): void
    {
        $response = $this->runApp(
            $method,
            $uri
        );
        $this->internalTestError($response, StatusCode::STATUS_UNAUTHORIZED);
    }

    /**
     * Test POST   /products 403 FORBIDDEN
     * Test PUT    /products/{productId} 403 FORBIDDEN => 404 NOT FOUND
     * Test DELETE /products/{productId} 403 FORBIDDEN => 404 NOT FOUND
     *
     * @param string $method
     * @param string $uri
     * @param int $statusCode
     * @return void
     * @dataProvider routeProvider403()
     */
    public function testProductStatus403Forbidden(string $method, string $uri, int $statusCode): void
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
