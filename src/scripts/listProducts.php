<?php

/**
 * src/scripts/listProducts.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

use TDW\ACiencia\Entity\Product;
use TDW\ACiencia\Utility\DoctrineConnector;

require __DIR__ . '/inicio.php';

try {
    $entityManager = DoctrineConnector::getEntityManager();
    $products = $entityManager->getRepository(Product::class)->findAll();
    $entityManager->close();
} catch (Throwable $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}

// Salida formato JSON
if (in_array('--json', $argv, false)) {
    echo json_encode(
        [ 'products' => $products ],
        JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
    );
    exit();
}

foreach ($products as $product) {
    echo $product . PHP_EOL;
}

echo sprintf("\nTotal: %d products.\n\n", count($products));
