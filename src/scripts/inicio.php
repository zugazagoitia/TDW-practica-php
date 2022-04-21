<?php

/**
 * src\scripts\inicio.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

$projectRootDir = dirname(__DIR__, 2);
require_once $projectRootDir . '/vendor/autoload.php';

// Carga las variables de entorno
TDW\ACiencia\Utility\Utils::loadEnv($projectRootDir);
