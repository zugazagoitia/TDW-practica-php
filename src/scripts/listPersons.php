<?php

/**
 * src/scripts/listPersons.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

use TDW\ACiencia\Entity\Person;
use TDW\ACiencia\Utility\DoctrineConnector;

require __DIR__ . '/inicio.php';

try {
    $entityManager = DoctrineConnector::getEntityManager();
    $persons = $entityManager->getRepository(Person::class)->findAll();
    $entityManager->close();
} catch (Throwable $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}

// Salida formato JSON
if (in_array('--json', $argv, false)) {
    echo json_encode(
        [ 'persons' => $persons ],
        JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
    );
    exit();
}

foreach ($persons as $person) {
    echo $person . PHP_EOL;
}

echo sprintf("\nTotal: %d persons.\n\n", count($persons));
