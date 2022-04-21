<?php

/**
 * src/scripts/removePerson.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

use TDW\ACiencia\Entity\Person;
use TDW\ACiencia\Utility\DoctrineConnector;

require __DIR__ . '/inicio.php';

if ($argc !== 2) {
    $texto = <<< ______USO

    *> Usage: ${argv[0]} <personId>
    Deletes the person specified by <personId>

______USO;
    die($texto);
}

try {
    $personId = (int) $argv[1];
    $entityManager = DoctrineConnector::getEntityManager();
    $person = $entityManager
        ->find(Person::class, $personId);
    if (null === $person) {
        exit('Person [' . $personId . '] not exist.' . PHP_EOL);
    }
    $entityManager->remove($person);
    $entityManager->flush();
} catch (Throwable $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}
