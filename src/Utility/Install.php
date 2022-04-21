<?php

/**
 * src/Utility/Install.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Utility;

use Composer\Script\Event;

/**
 * Class Install
 */
abstract class Install
{
    public static function preUpdateSchema(Event $event): ?bool
    {
        // provides access to the current ComposerIOConsoleIO
        // stream for terminal input/output
        $io = $event->getIO();
        if (
            !$io->isInteractive()
            || $io->askConfirmation(
                'Este comando eliminará el contenido de las tablas. ¿Desea continuar? (y/N)',
                false
            )
        ) {
            // ok, continue on to composer install
            return true;
        }
        // exit composer and terminate installation process
        exit;
    }

    /**
     * PostInstall command
     *
     * @param Event $event event
     *
     * @return bool
     * @throws \Exception
     */
    public static function postInstall(Event $event): bool
    {
        // Load the environment/configuration variables
        Utils::loadEnv(dirname(__DIR__, 2));

        if (
            !isset(
                $_ENV['ADMIN_USER_NAME'],
                $_ENV['ADMIN_USER_EMAIL'],
                $_ENV['ADMIN_USER_PASSWD']
            )
        ) {
            fwrite(STDERR, 'Faltan variables de entorno por definir' . PHP_EOL);
            exit(1);
        }

        // Create/update tables in the database
        Utils::updateSchema();
        $event->getIO()->write('>> Database UPDATED');

        return (bool) Utils::loadUserData(
            $_ENV['ADMIN_USER_NAME'],
            $_ENV['ADMIN_USER_EMAIL'],
            $_ENV['ADMIN_USER_PASSWD'],
            true
        );
    }
}
