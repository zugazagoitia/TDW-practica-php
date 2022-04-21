<?php

/**
 * src/Entity/Person.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="person",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name = "Person_name_uindex",
 *              columns = {"name"}
 *          )
 *      }
 * )
 */
class Person extends Element
{
    /**
     * @ORM\ManyToMany(
     *     targetEntity="Entity",
     *     mappedBy="persons"
     *     )
     * @ORM\OrderBy({ "id" = "ASC" })
     */
    protected Collection $entities;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Product",
     *     mappedBy="persons"
     *     )
     * @ORM\OrderBy({ "id" = "ASC" })
     */
    protected Collection $products;

    /**
     * Person constructor.
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
        parent::__construct($name, $birthDate, $deathDate, $imageUrl, $wikiUrl);
        $this->entities = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    // Entities

    /**
     * @return Entity[]
     */
    public function getEntities(): array
    {
        return $this->entities->getValues();
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function containsEntity(Entity $entity): bool
    {
        return $this->entities->contains($entity);
    }

    /**
     * @param Entity $entity
     *
     * @return void
     */
    public function addEntity(Entity $entity): void
    {
        $entity->addPerson($this);
        $this->entities->add($entity);
    }

    /**
     * @param Entity $entity
     *
     * @return void
     */
    public function removeEntity(Entity $entity): void
    {
        $this->entities->removeElement($entity);
        $entity->removePerson($this);
    }

    // Products

    /**
     * @return Product[]
     */
    public function getProducts(): array
    {
        return $this->products->getValues();
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function containsProduct(Product $product): bool
    {
        return $this->products->contains($product);
    }

    /**
     * @param Product $product
     *
     * @return void
     */
    public function addProduct(Product $product): void
    {
        $this->products->add($product);
        $product->addPerson($this);
    }

    /**
     * @param Product $product
     *
     * @return void
     */
    public function removeProduct(Product $product): void
    {
        $this->products->removeElement($product);
        $product->removePerson($this);
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString(): string
    {
        return sprintf(
            '%s products="%s", entities="%s")]',
            parent::__toString(),
            $this->getCodesTxt($this->getProducts()),
            $this->getCodesTxt($this->getEntities())
        );
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        $data['products'] = $this->getProducts() ? $this->getCodes($this->getProducts()) : null;
        $data['entities'] = $this->getEntities() ? $this->getCodes($this->getEntities()) : null;

        return ['person' => $data];
    }
}
