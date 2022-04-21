<?php

/**
 * src/scripts/removeProduct.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

use TDW\ACiencia\Entity\Product;
use TDW\ACiencia\Utility\DoctrineConnector;

require __DIR__ . '/inicio.php';

if ($argc !== 2) {
    $texto = <<< ______USO

    *> Usage: ${argv[0]} <productId>
    Deletes the product specified by <productId>

______USO;
    die($texto);
}

try {
    $productId = (int) $argv[1];
    $entityManager = DoctrineConnector::getEntityManager();
    $product = $entityManager
        ->find(Product::class, $productId);
    if (null === $product) {
        exit('Product [' . $productId . '] not exist.' . PHP_EOL);
    }
    $entityManager->remove($product);
    $entityManager->flush();
} catch (Throwable $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}
