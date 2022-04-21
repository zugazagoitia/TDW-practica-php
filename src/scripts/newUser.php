<?php

/**
 * src/scripts/newUser.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

require __DIR__ . '/inicio.php';

use TDW\ACiencia\Entity\Role;
use TDW\ACiencia\Entity\User;
use TDW\ACiencia\Utility\DoctrineConnector;

try {
    $num = random_int(0, 100000);
    $role = Role::ROLES[$num % 2];
    $nombre = 'user-' . $num;

    $entityManager = DoctrineConnector::getEntityManager();
    $user = new User($nombre, $nombre . '@example.com', $nombre, $role);

    $entityManager->persist($user);
    $entityManager->flush();
    echo 'Created User with ID ' . $user->getId() . PHP_EOL;
} catch (Throwable $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}
