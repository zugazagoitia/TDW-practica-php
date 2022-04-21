<?php

/**
 * src/scripts/createProduct.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

use TDW\ACiencia\Entity\Product;
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
    $product = $entityManager->getRepository(Product::class)->findOneBy(['name' => $name]);
    if (null !== $product) {
        throw new Exception("Product $name already exists" . PHP_EOL);
    }

    $product = new Product($name);
    $entityManager->persist($product);
    $entityManager->flush();
    echo 'Created Product with ID ' . $product->getId() . PHP_EOL;

    $entityManager->close();
} catch (Throwable $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}
