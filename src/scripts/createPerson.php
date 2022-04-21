<?php

/**
 * src/scripts/createPerson.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

use TDW\ACiencia\Entity\Person;
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
    $person = $entityManager->getRepository(Person::class)->findOneBy(['name' => $name]);
    if (null !== $person) {
        throw new Exception("Person $name already exists" . PHP_EOL);
    }

    $person = new Person($name);
    $entityManager->persist($person);
    $entityManager->flush();
    echo 'Created Person with ID ' . $person->getId() . PHP_EOL;

    $entityManager->close();
} catch (Throwable $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}
