<?php

/**
 * tests/Entity/UserTest.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\Test\ACiencia\Entity;

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use TDW\ACiencia\Entity\Role;
use TDW\ACiencia\Entity\User;
use UnexpectedValueException;

/**
 * Class UserTest
 *
 * @group   users
 */
class UserTest extends TestCase
{
    protected static User $user;

    private static \Faker\Generator $faker;

    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     */
    public static function setUpBeforeClass(): void
    {
        self::$user  = new User();
        self::$faker = Factory::create('es_ES');
    }

    /**
     * @return void
     */
    public function testConstructor(): void
    {
        self::$user = new User();
        self::assertSame(0, self::$user->getId());
        self::assertEmpty(self::$user->getUsername());
        self::assertEmpty(self::$user->getEmail());
        self::assertTrue(self::$user->hasRole(Role::ROLE_READER));
        self::assertFalse(self::$user->hasRole(Role::ROLE_WRITER));
    }

    public function testGetId(): void
    {
        self::assertSame(0, self::$user->getId());
    }

    /**
     * @depends testConstructor
     */
    public function testGetSetUsername(): void
    {
        static::assertEmpty(self::$user->getUsername());
        $username = self::$faker->userName();
        self::$user->setUsername($username);
        static::assertSame($username, self::$user->getUsername());
    }

    public function testGetSetEmail(): void
    {
        $userEmail = self::$faker->email();
        static::assertEmpty(self::$user->getEmail());
        self::$user->setEmail($userEmail);
        static::assertSame($userEmail, self::$user->getEmail());
    }

    public function testRoles(): void
    {
        self::$user->setRole(ucfirst(Role::ROLE_READER));
        self::assertTrue(self::$user->hasRole(Role::ROLE_READER));
        self::assertFalse(self::$user->hasRole(Role::ROLE_WRITER));
        self::assertTrue(in_array(Role::ROLE_READER, self::$user->getRoles()));
        self::assertFalse(in_array(Role::ROLE_WRITER, self::$user->getRoles()));

        self::$user->setRole(Role::ROLE_WRITER);
        self::assertTrue(self::$user->hasRole(strtoupper(Role::ROLE_WRITER)));
        self::assertTrue(in_array(Role::ROLE_READER, self::$user->getRoles()));
        self::assertTrue(in_array(Role::ROLE_WRITER, self::$user->getRoles()));
    }

    public function testRoleExpectExceptionOutOfRange(): void
    {
        $this->expectException(UnexpectedValueException::class);
        self::$user->setRole(self::$faker->word());
    }

    public function testGetSetValidatePassword(): void
    {
        $password = self::$faker->password();
        self::$user->setPassword($password);
        self::assertTrue(password_verify($password, self::$user->getPassword()));
        self::assertTrue(self::$user->validatePassword($password));
    }

    /**
     * @depends testGetSetUsername
     */
    public function testToString(): void
    {
        $username = self::$faker->userName();
        self::$user->setUsername($username);
        self::assertStringContainsString($username, self::$user->__toString());
    }

    public function testJsonSerialize(): void
    {
        $json = json_encode(self::$user, JSON_ERROR_NONE);
        self::assertJson((string) $json);
    }
}
