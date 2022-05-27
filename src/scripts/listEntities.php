65<?php

/**
 * src/scripts/listEntities.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

use TDW\ACiencia\Entity\Entity;
use TDW\ACiencia\Utility\DoctrineConnector;

require __DIR__ . '/inicio.php';

try {
    $entityManager = DoctrineConnector::getEntityManager();
    $entities = $entityManager->getRepository(Entity::class)->findAll();
    $entityManager->close();
} catch (Throwable $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}

// Salida formato JSON
if (in_array('--json', $argv, false)) {
    echo json_encode(
        [ 'entities' => $entities ],
        JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
    );
    exit();
}

foreach ($entities as $entity) {
    echo $entity . PHP_EOL;
}

echo sprintf("\nTotal: %d entities.\n\n", count($entities));
