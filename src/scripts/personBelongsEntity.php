<?php

/**
 * src/scripts/personBelongsEntity.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

use TDW\ACiencia\Entity\Person;
use TDW\ACiencia\Entity\Entity;
use TDW\ACiencia\Utility\DoctrineConnector;

require __DIR__ . '/inicio.php';

if (3 !== $argc) {
    $fich = basename(__FILE__);
    echo <<< MARCA_FIN

Usage: $fich <personId> <entityId>
 
MARCA_FIN;
    exit(0);
}

$personId = (int) $argv[1];
$entityId = (int) $argv[2];

try {
    $entityManager = DoctrineConnector::getEntityManager();
    /** @var Person $person */
    $person = $entityManager->find(Person::class, $personId);
    if (null === $person) {
        throw new Exception("Person $personId not exist" . PHP_EOL);
    }
    /** @var Entity $entity */
    $entity = $entityManager->find(Entity::class, $entityId);
    if (null === $entity) {
        throw new Exception("Entity $entityId not exist" . PHP_EOL);
    }

    $person->addEntity($entity);
    $entityManager->flush();
    $entityManager->close();
    echo 'Person ID=' . $person->getId() . ': added entity ' . $entityId . PHP_EOL;
} catch (Throwable $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}
