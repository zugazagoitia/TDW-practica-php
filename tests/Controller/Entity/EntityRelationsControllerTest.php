<?php

/**
 * tests/Controller/Entity/EntityRelationsControllerTest.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\Test\ACiencia\Controller\Entity;

use Doctrine\ORM\EntityManagerInterface;
use TDW\ACiencia\Controller\Entity\EntityController;
use TDW\ACiencia\Controller\Entity\EntityRelationsController;
use TDW\ACiencia\Entity\Entity;
use TDW\ACiencia\Entity\Person;
use TDW\ACiencia\Entity\Product;
use TDW\ACiencia\Utility\DoctrineConnector;
use TDW\ACiencia\Utility\Utils;
use TDW\Test\ACiencia\Controller\BaseTestCase;

/**
 * Class EntityRelationsControllerTest
 */
final class EntityRelationsControllerTest extends BaseTestCase
{
    /** @var string Path para la gestión de entityas */
    protected const RUTA_API = '/api/v1/entities';

    /** @var array Admin data */
    protected static array $writer;

    /** @var array reader user data */
    protected static array $reader;

    protected static EntityManagerInterface $entityManager;

    private static Entity $entity;
    private static Person $person;
    private static Product $product;

    /**
     * Se ejecuta una vez al inicio de las pruebas de la clase UserControllerTest
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // load user admin fixtures
        self::$writer = [
            'username' => getenv('ADMIN_USER_NAME'),
            'email'    => getenv('ADMIN_USER_EMAIL'),
            'password' => getenv('ADMIN_USER_PASSWD'),
        ];

        self::$writer['id'] = Utils::loadUserData(
            self::$writer['username'],
            self::$writer['email'],
            self::$writer['password'],
            true
        );

        // load user reader fixtures
        self::$reader = [
            'username' => self::$faker->userName(),
            'email'    => self::$faker->email(),
            'password' => self::$faker->password(),
        ];
        self::$reader['id'] = Utils::loadUserData(
            self::$reader['username'],
            self::$reader['email'],
            self::$reader['password'],
            false
        );

        // create and insert fixtures
        self::$entity = new Entity(self::$faker->word());
        self::$person  = new Person(self::$faker->company());
        self::$product  = new Product(self::$faker->name());

        self::$entityManager = DoctrineConnector::getEntityManager();
        self::$entityManager->persist(self::$entity);
        self::$entityManager->persist(self::$person);
        self::$entityManager->persist(self::$product);
        self::$entityManager->flush();
    }

    public function testGetEntitiesTag()
    {
        self::assertSame(
            EntityController::getEntitiesTag(),
            EntityRelationsController::getEntitiesTag()
        );
    }

    // *******************
    // Entity -> Persons
    // *******************
    /**
     * PUT /entities/{entityId}/persons/add/{stuffId}
     */
    public function testAddPerson209()
    {
        self::$writer['authHeader'] = $this->getTokenHeaders(self::$writer['username'], self::$writer['password']);
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . self::$entity->getId()
                . '/persons/add/' . self::$person->getId(),
            null,
            self::$writer['authHeader']
        );
        self::assertSame(209, $response->getStatusCode());
        self::assertJson($response->getBody()->getContents());
    }

    /**
     * GET /entities/{entityId}/persons 200 Ok
     *
     * @depends testAddPerson209
     */
    public function testGetPersons200OkWithElements(): void
    {
        self::$reader['authHeader'] = $this->getTokenHeaders(self::$reader['username'], self::$reader['password']);
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . self::$entity->getId() . '/persons',
            null,
            self::$reader['authHeader']
        );
        self::assertSame(200, $response->getStatusCode());
        $r_body = $response->getBody()->getContents();
        self::assertJson($r_body);
        $responsePersons = json_decode($r_body, true);
        self::assertArrayHasKey('persons', $responsePersons);
        self::assertSame(
            self::$person->getName(),
            $responsePersons['persons'][0]['person']['name']
        );
    }

    /**
     * PUT /entities/{entityId}/persons/rem/{stuffId}
     *
     * @depends testGetPersons200OkWithElements
     */
    public function testRemovePerson209()
    {
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . self::$entity->getId()
            . '/persons/rem/' . self::$person->getId(),
            null,
            self::$writer['authHeader']
        );
        self::assertSame(209, $response->getStatusCode());
        $r_body = $response->getBody()->getContents();
        self::assertJson($r_body);
        $responseEntity = json_decode($r_body, true);
        self::assertArrayHasKey('persons', $responseEntity['entity']);
        self::assertEmpty($responseEntity['entity']['persons']);
    }

    /**
     * GET /entities/{entityId}/persons 200 Ok - Empty
     *
     * @depends testRemovePerson209
     */
    public function testGetPersons200OkEmpty(): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . self::$entity->getId() . '/persons',
            null,
            self::$reader['authHeader']
        );
        self::assertSame(200, $response->getStatusCode());
        $r_body = $response->getBody()->getContents();
        self::assertJson($r_body);
        $responsePersons = json_decode($r_body, true);
        self::assertArrayHasKey('persons', $responsePersons);
        self::assertEmpty($responsePersons['persons']);
    }

    // ******************
    // Entity -> Products
    // ******************
    /**
     * PUT /entities/{entityId}/products/add/{stuffId}
     */
    public function testAddProduct209()
    {
        self::$writer['authHeader'] = $this->getTokenHeaders(self::$writer['username'], self::$writer['password']);
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . self::$entity->getId()
            . '/products/add/' . self::$product->getId(),
            null,
            self::$writer['authHeader']
        );
        self::assertSame(209, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
    }

    /**
     * GET /entities/{entityId}/products 200 Ok
     *
     * @depends testAddProduct209
     */
    public function testGetProducts200OkWithElements(): void
    {
        self::$reader['authHeader'] = $this->getTokenHeaders(self::$reader['username'], self::$reader['password']);
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . self::$entity->getId() . '/products',
            null,
            self::$reader['authHeader']
        );
        self::assertSame(200, $response->getStatusCode());
        $r_body = $response->getBody()->getContents();
        self::assertJson($r_body);
        $responseProducts = json_decode($r_body, true);
        self::assertArrayHasKey('products', $responseProducts);
        self::assertSame(
            self::$product->getName(),
            $responseProducts['products'][0]['product']['name']
        );
    }

    /**
     * PUT /entities/{entityId}/products/rem/{stuffId}
     *
     * @depends testGetProducts200OkWithElements
     */
    public function testRemoveProduct209()
    {
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . self::$entity->getId()
            . '/products/rem/' . self::$product->getId(),
            null,
            self::$writer['authHeader']
        );
        self::assertSame(209, $response->getStatusCode());
        $r_body = $response->getBody()->getContents();
        self::assertJson($r_body);
        $responseEntities = json_decode($r_body, true);
        self::assertArrayHasKey('products', $responseEntities['entity']);
        self::assertEmpty($responseEntities['entity']['products']);
    }

    /**
     * GET /entities/{entityId}/products 200 Ok - Empty
     *
     * @depends testRemoveProduct209
     */
    public function testGetProducts200OkEmpty(): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . self::$entity->getId() . '/products',
            null,
            self::$reader['authHeader']
        );
        self::assertSame(200, $response->getStatusCode());
        $r_body = $response->getBody()->getContents();
        self::assertJson($r_body);
        $responseProducts = json_decode($r_body, true);
        self::assertArrayHasKey('products', $responseProducts);
        self::assertEmpty($responseProducts['products']);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param int $status
     * @param string $user
     * @return void
     *
     * @dataProvider routeExceptionProvider()
     */
    public function testEntityRelationshipErrors(string $method, string $uri, int $status, string $user = ''): void
    {
        $requestingUser = match ($user) {
            'admin'  => self::$writer,
            'reader' => self::$reader,
            default  => ['username' => '', 'password' => '']
        };

        $response = $this->runApp(
            $method,
            $uri,
            null,
            $this->getTokenHeaders($requestingUser['username'], $requestingUser['password'])
        );
        $this->internalTestError($response, $status);
    }

    // --------------
    // DATA PROVIDERS
    // --------------

    /**
     * Route provider (expected status: 404 NOT FOUND)
     *
     * @return array [ method, url, status, user ]
     */
    public static function routeExceptionProvider(): array
    {
        return [
            // 401
            // 'getPersons401'       => [ 'GET', self::RUTA_API . '/1/persons',       401],
            'putAddPerson401'     => [ 'PUT', self::RUTA_API . '/1/persons/add/1', 401],
            'putRemovePerson401'  => [ 'PUT', self::RUTA_API . '/1/persons/rem/1', 401],
            // 'getProducts401'      => [ 'GET', self::RUTA_API . '/1/products',        401],
            'putAddProduct401'    => [ 'PUT', self::RUTA_API . '/1/products/add/1',  401],
            'putRemoveProduct401' => [ 'PUT', self::RUTA_API . '/1/products/rem/1',  401],

            // 403
            'putAddPerson403'     => [ 'PUT', self::RUTA_API . '/1/persons/add/1', 403, 'reader'],
            'putRemovePerson403'  => [ 'PUT', self::RUTA_API . '/1/persons/rem/1', 403, 'reader'],
            'putAddProduct403'    => [ 'PUT', self::RUTA_API . '/1/products/add/1',  403, 'reader'],
            'putRemoveProduct403' => [ 'PUT', self::RUTA_API . '/1/products/rem/1',  403, 'reader'],

            // 404
            'getPersons404'       => [ 'GET', self::RUTA_API . '/0/persons',       404, 'admin'],
            'putAddPerson404'     => [ 'PUT', self::RUTA_API . '/0/persons/add/1', 404, 'admin'],
            'putRemovePerson404'  => [ 'PUT', self::RUTA_API . '/0/persons/rem/1', 404, 'admin'],
            'getProducts404'      => [ 'GET', self::RUTA_API . '/0/products',        404, 'admin'],
            'putAddProduct404'    => [ 'PUT', self::RUTA_API . '/0/products/add/1',  404, 'admin'],
            'putRemoveProduct404' => [ 'PUT', self::RUTA_API . '/0/products/rem/1',  404, 'admin'],

            // 406
            'putAddPerson406'     => [ 'PUT', self::RUTA_API . '/1/persons/add/100', 406, 'admin'],
            'putRemovePerson406'  => [ 'PUT', self::RUTA_API . '/1/persons/rem/100', 406, 'admin'],
            'putAddProduct406'    => [ 'PUT', self::RUTA_API . '/1/products/add/100',  406, 'admin'],
            'putRemoveProduct406' => [ 'PUT', self::RUTA_API . '/1/products/rem/100',  406, 'admin'],
        ];
    }
}
