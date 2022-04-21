<?php

/**
 * src/Entity/Element.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Class Element
 */
abstract class Element implements JsonSerializable
{
    /**
     * @ORM\Column(
     *     name="id",
     *     type="integer",
     *     nullable=false
     *     )
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\Column(
     *     name="name",
     *     type="string",
     *     length=80,
     *     unique=true,
     *     nullable=false
     *     )
     */
    protected string $name;

    /**
     * @ORM\Column(
     *     name="birthdate",
     *     type="datetime",
     *     nullable=true
     *     )
     */
    protected DateTime | null $birthDate = null;

    /**
     * @ORM\Column(
     *     name="deathdate",
     *     type="datetime",
     *     nullable=true
     *     )
     */
    protected DateTime | null $deathDate = null;

    /**
     * @ORM\Column(
     *     name="image_url",
     *     type="string",
     *     length=2047,
     *     nullable=true
     *     )
     */
    protected string | null $imageUrl = null;

    /**
     * @ORM\Column(
     *     name="wiki_url",
     *     type="string",
     *     length=2047,
     *     nullable=true
     *     )
     */
    protected string | null $wikiUrl = null;

    /**
     * Element constructor.
     * @param string $name
     * @param DateTime|null $birthDate
     * @param DateTime|null $deathDate
     * @param string|null $imageUrl
     * @param string|null $wikiUrl
     */
    public function __construct(
        string $name,
        ?DateTime $birthDate = null,
        ?DateTime $deathDate = null,
        ?string $imageUrl = null,
        ?string $wikiUrl = null
    ) {
        $this->id = 0;
        $this->name = $name;
        $this->birthDate = $birthDate;
        $this->deathDate = $deathDate;
        $this->imageUrl = $imageUrl;
        $this->wikiUrl = $wikiUrl;
    }

    /**
     * @return int
     */
    final public function getId(): int
    {
        return $this->id;
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
    final public function getDeathDate(): ?DateTime
    {
        return $this->deathDate;
    }

    /**
     * @param DateTime|null $deathDate
     * @return void
     */
    final public function setDeathDate(?DateTime $deathDate): void
    {
        $this->deathDate = $deathDate;
    }

    /**
     * @return string|null
     */
    final public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    /**
     * @param string|null $imageUrl
     * @return void
     */
    final public function setImageUrl(?string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return string|null
     */
    final public function getWikiUrl(): ?string
    {
        return $this->wikiUrl;
    }

    /**
     * @param string|null $wikiUrl
     * @return void
     */
    final public function setWikiUrl(?string $wikiUrl): void
    {
        $this->wikiUrl = $wikiUrl;
    }

    /**
     * @param array $collection
     *
     * @return array Ids in collection
     */
    protected function getCodes(array $collection): array
    {
        return array_map(
            fn(object $object) => $object->getId(),
            $collection
        );
    }

    /**
     * @param Element[] $collection
     *
     * @return string String representation of Collection
     */
    protected function getCodesTxt(array $collection): string
    {
        $codes = $this->getCodes($collection);
        return sprintf('[%s]', implode(', ', $codes));
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
        $deathdate = $this->getDeathDate()?->format('Y-m-d') ?? 'null';
        return sprintf(
            '[%s: (id=%04d, name="%s", birthDate="%s", deathDate="%s",',
            basename(get_class($this)),
            $this->getId(),
            $this->getName(),
            $birthdate,
            $deathdate
        );
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'birthDate' => $this->getBirthDate()?->format('Y-m-d') ?? null,
            'deathDate' => $this->getDeathDate()?->format('Y-m-d') ?? null,
            'imageUrl'  => $this->getImageUrl() ?? null,
            'wikiUrl'  => $this->getWikiUrl() ?? null,
        ];
    }
}
