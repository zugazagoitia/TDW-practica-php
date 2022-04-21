<?php

/**
 * src/scripts/createEntity.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

use TDW\ACiencia\Entity\Entity;
use TDW\ACiencia\Utility\DoctrineConnector;

require __DIR__ . '/inicio.php';

if (2 !== $argc) {
    $fich = basename(__FILE__);
    echo <<< MARCA_FIN

Usage: $fich <name>
 
MARCA_FIN;
    exit(0);
}

$name = $argv[1];

try {
    $entityManager = DoctrineConnector::getEntityManager();
    $entity = $entityManager->getRepository(Entity::class)->findOneBy(['name' => $name]);
    if (null !== $entity) {
        throw new Exception("Entity $name already exists" . PHP_EOL);
    }

    $entity = new Entity($name);
    $entityManager->persist($entity);
    $entityManager->flush();
    echo 'Created Entity with ID ' . $entity->getId() . PHP_EOL;

    $entityManager->close();
} catch (Throwable $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}
