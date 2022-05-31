<?php

/**
 * src/Entity/User.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use UnexpectedValueException;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     name                 = "user",
 *     uniqueConstraints    = {
 *          @ORM\UniqueConstraint(
 *              name="IDX_UNIQ_USERNAME", columns={ "username" }
 *          ),
 *          @ORM\UniqueConstraint(
 *              name="IDX_UNIQ_EMAIL", columns={ "email" }
 *          )
 *      }
 *     )
 */
class User implements JsonSerializable
{
    /**
     * @ORM\Column(
     *     name     = "id",
     *     type     = "integer",
     *     nullable = false
     *     )
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\Column(
     *     name     = "username",
     *     type     = "string",
     *     length   = 32,
     *     unique   = true,
     *     nullable = false
     *     )
     */
    protected string $username;

    /**
     * @ORM\Column(
     *     name     = "email",
     *     type     = "string",
     *     length   = 60,
     *     unique   = true,
     *     nullable = false
     *     )
     */
    protected string $email;

    /**
     * @ORM\Column(
     *     name     = "password",
     *     type     = "string",
     *     length   = 60,
     *     nullable = false
     *     )
     */
    protected string $password_hash;

    /**
     * @ORM\Column(
     *     name="birthdate",
     *     type="datetime",
     *     nullable=true
     *     )
     */
    protected DateTime|null $birthDate = null;

    /**
     * @ORM\Column(
     *     name="registerTime",
     *     type="datetime",
     *     nullable=true
     *     )
     */
    protected DateTime|null $registerTime = null;

    /**
     * @ORM\Column(
     *     name="name",
     *     type="string",
     *     length=80,
     *     nullable=true
     *     )
     */
    protected string $name;

    /**
     * @ORM\Column(
     *     name     = "active",
     *     type     = "boolean",
     *     nullable = false
     *     )
     */
    protected bool $active;


    /**
     * @ORM\Embedded(
     *     class="TDW\ACiencia\Entity\Role"
     * )
     */
    protected Role $role;


    /**
     * User constructor.
     *
     * @param string $username username
     * @param string $email email
     * @param string $password password
     * @param string $role Role::ROLE_READER | Role::ROLE_WRITER
     * @param DateTime|null $birthDate
     * @param string $name
     * @param bool $active
     * @throws UnexpectedValueException
     */
    public function __construct(
        string    $username = '',
        string    $email = '',
        string    $password = '',
        string    $role = Role::ROLE_READER,
        ?DateTime $birthDate = null,
        string    $name = '',
        bool      $active = true,
    )
    {
        $this->id = 0;
        $this->username = $username;
        $this->email = $email;
        $this->setPassword($password);
        $this->birthDate = $birthDate;
        $this->name = $name;
        $this->active = $active;
        $this->registerTime = new DateTime();
        try {
            $this->setRole($role);
        } catch (UnexpectedValueException) {
            throw new UnexpectedValueException('Unexpected Role');
        }
    }



    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set username
     *
     * @param string $username username
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email email
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @param string $role
     * @return boolean
     */
    public function hasRole(string $role): bool
    {
        return $this->role->hasRole($role);
    }

    /**
     * @param string $role [ Role::ROLE_READER | Role::ROLE_WRITER ]
     * @return void
     * @throws UnexpectedValueException
     */
    public function setRole(string $role): void
    {
        // Object types are compared by reference, not by value.
        // Doctrine updates this values if the reference changes and therefore
        // behaves as if these objects are immutable value objects.
        $this->role = new Role($role);
    }

    /**
     * @return array ['reader'] | ['reader', 'writer']
     */
    public function getRoles(): array
    {
        $roles = array_filter(
            Role::ROLES,
            fn($myRole) => $this->hasRole($myRole)
        );

        return $roles;
    }

    /**
     * Get the hashed password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password_hash;
    }

    /**
     * @param string $password password
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password_hash = strval(password_hash($password, PASSWORD_DEFAULT));
    }

    /**
     * Verifies that the given hash matches the user password.
     *
     * @param string $password password
     * @return boolean
     */
    public function validatePassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    /**
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param boolean $active active
     * @return void
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    final public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    final public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return DateTime|null
     */
    final public function getBirthDate(): ?DateTime
    {
        return $this->birthDate;
    }

    /**
     * @param DateTime|null $birthDate
     * @return void
     */
    final public function setBirthDate(?DateTime $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    /**
     * @return DateTime|null
     */
    public function getRegisterTime(): ?DateTime
    {
        return $this->registerTime;
    }

    /**
     * @param DateTime|null $registerTime
     */
    public function setRegisterTime(?DateTime $registerTime): void
    {
        $this->registerTime = $registerTime;
    }





    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString(): string
    {
        $birthdate = $this->getBirthDate()?->format('Y-m-d') ?? 'null';

        $registerTime = $this->getRegisterTime()?->format('Y-m-d') ?? 'null';

        return
            sprintf(
                '[%s: (id=%04d, username="%s", email="%s", active="%s", name="%s", birthDate="%s",role="%s",registerTime="%s")]',
                basename(self::class),
                $this->getId(),
                $this->getUsername(),
                $this->getEmail(),
                var_export($this->isActive(), true),
                $this->getName(),
                $birthdate,
                $this->role,
                $registerTime
            );
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array
    {
        return [
            'user' => [
                'id' => $this->getId(),
                'username' => $this->getUsername(),
                'email' => $this->getEmail(),
                'active' => $this->isActive(),
                'birthDate' => $this->getBirthDate()?->format('Y-m-d') ?? null,
                'name' => $this->getName(),
                'role' => $this->role->__toString(),
                'registerTime' => $this->getRegisterTime()?->format('Y-m-d') ?? null,
            ]
        ];
    }
}
