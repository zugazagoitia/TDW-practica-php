<?php

/**
 * ./config/cli-config.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es ETS de Ingeniería de Sistemas Informáticos
 */

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use TDW\ACiencia\Utility\DoctrineConnector;
use TDW\ACiencia\Utility\Utils;

// Load env variables from .env + (.docker || .local )
Utils::loadEnv(dirname(__DIR__));

$entityManager = DoctrineConnector::getEntityManager();

return ConsoleRunner::createHelperSet($entityManager);
