<?php

/**
 * src/Entity/Role.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use UnexpectedValueException;

/**
 * Class Role
 *
 * @ORM\Embeddable
 */
class Role implements JsonSerializable
{
    // scope names
    public const ROLE_READER = 'reader';
    public const ROLE_WRITER = 'writer';
    public const ROLES = [
        0 => self::ROLE_READER,
        1 => self::ROLE_WRITER,
    ];

    /**
     * @ORM\Column(
     *     name="value",
     *     type="smallint"
     * )
     */
    private int $role;

    /**
     * Role constructor.
     * @param string $role
     * @throws UnexpectedValueException
     */
    public function __construct(string $role = self::ROLE_READER)
    {
        $this->setRole($role);
    }

    /**
     * @param string $role [ Role::ROLE_READER | Role::ROLE_WRITER ]
     * @return void
     */
    protected function setRole(string $role): void
    {
        $role = strtolower($role);
        if (!in_array($role, self::ROLES, true)) {
            throw new UnexpectedValueException('Unexpected Role');
        }
        $this->role = (int) array_search($role, self::ROLES);
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return match (strtolower($role)) {
            self::ROLE_READER => true,
            self::ROLE_WRITER => (self::ROLES[$this->role] === self::ROLE_WRITER),
            default => false
        };
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link https://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString(): string
    {
        return self::ROLES[$this->role];
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function jsonSerialize(): array
    {
        return [
            'role' => $this->__toString()
            ];
    }
}
