<?php

/**
 * tests/Entity/ProductTest.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\Test\ACiencia\Entity;

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use TDW\ACiencia\Entity\Entity;
use TDW\ACiencia\Entity\Person;
use TDW\ACiencia\Entity\Product;

/**
 * Class ProductTest
 *
 * @group   products
 * @coversDefaultClass \TDW\ACiencia\Entity\Product
 */
class ProductTest extends TestCase
{
    protected static Product $product;

    private static \Faker\Generator $faker;

    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     */
    public static function setUpBeforeClass(): void
    {
        self::$faker = Factory::create('es_ES');
        self::$product  = new Product('');
    }

    /**
     * @return void
     */
    public function testConstructor(): void
    {
        $name = self::$faker->name();
        self::$product = new Product($name);
        self::assertSame(0, self::$product->getId());
        self::assertSame(
            $name,
            self::$product->getName()
        );
        self::assertEmpty(self::$product->getEntities());
        self::assertEmpty(self::$product->getPersons());
    }

    public function testGetId(): void
    {
        self::assertSame(0, self::$product->getId());
    }

    public function testGetSetProductName(): void
    {
        $productname = self::$faker->name();
        self::$product->setName($productname);
        static::assertSame(
            $productname,
            self::$product->getName()
        );
    }

    public function testGetSetBirthDate(): void
    {
        $birthDate = self::$faker->dateTime();
        self::$product->setBirthDate($birthDate);
        static::assertSame(
            $birthDate,
            self::$product->getBirthDate()
        );
    }

    public function testGetSetDeathDate(): void
    {
        $deathDate = self::$faker->dateTime();
        self::$product->setDeathDate($deathDate);
        static::assertSame(
            $deathDate,
            self::$product->getDeathDate()
        );
    }

    public function testGetSetImageUrl(): void
    {
        $imageUrl = self::$faker->url();
        self::$product->setImageUrl($imageUrl);
        static::assertSame(
            $imageUrl,
            self::$product->getImageUrl()
        );
    }

    public function testGetSetWikiUrl(): void
    {
        $wikiUrl = self::$faker->url();
        self::$product->setWikiUrl($wikiUrl);
        static::assertSame(
            $wikiUrl,
            self::$product->getWikiUrl()
        );
    }

    public function testGetAddContainsRemoveEntities(): void
    {
        self::assertEmpty(self::$product->getEntities());
        $entity = new Entity(self::$faker->slug());
        self::$product->addEntity($entity);
        self::$product->addEntity($entity); // CCoverage

        self::assertNotEmpty(self::$product->getEntities());
        self::assertTrue(self::$product->containsEntity($entity));

        self::$product->removeEntity($entity);
        self::assertFalse(self::$product->containsEntity($entity));
        self::assertEmpty(self::$product->getEntities());
        self::assertNull(self::$product->removeEntity($entity));
    }

    public function testGetAddContainsRemovePersons(): void
    {
        self::assertEmpty(self::$product->getPersons());
        $person = new Person(self::$faker->slug());
        self::$product->addPerson($person);
        self::$product->addPerson($person);  // CCoverage

        self::assertNotEmpty(self::$product->getPersons());
        self::assertTrue(self::$product->containsPerson($person));

        self::$product->removePerson($person);
        self::assertFalse(self::$product->containsPerson($person));
        self::assertEmpty(self::$product->getPersons());
        self::assertNull(self::$product->removePerson($person));
    }

    public function testToString(): void
    {
        $productName = self::$faker->words(3, true);
        $birthDate = self::$faker->dateTime();
        $deathDate = self::$faker->dateTime();
        self::$product->setName($productName);
        self::$product->setBirthDate($birthDate);
        self::$product->setDeathDate($deathDate);
        self::assertStringContainsString(
            $productName,
            self::$product->__toString()
        );
        self::assertStringContainsString(
            $birthDate->format('Y-m-d'),
            self::$product->__toString()
        );
        self::assertStringContainsString(
            $deathDate->format('Y-m-d'),
            self::$product->__toString()
        );
    }

    public function testJsonSerialize(): void
    {
        $jsonStr = json_encode(self::$product);
        self::assertJson($jsonStr);
    }
}
