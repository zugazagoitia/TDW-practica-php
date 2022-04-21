<?php

use Doctrine\ORM\EntityManager;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Selective\BasePath\BasePathMiddleware;
use Selective\Config\Configuration;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;
use TDW\ACiencia\Auth\JwtAuth;
use TDW\ACiencia\Factory\LoggerFactory;
use TDW\ACiencia\Utility\DoctrineConnector;

return [
    // Application settings
    Configuration::class => function () {
        return new Configuration(require __DIR__ . '/settings.php');
    },

    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);

        return AppFactory::create();
    },

    // HTTP factories
    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getResponseFactory();
    },

    // The Slim RouterParser
    RouteParserInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getRouteCollector()->getRouteParser();
    },

    // The logger factory
    LoggerFactory::class => function (ContainerInterface $container) {
        return new LoggerFactory($container->get(Configuration::class)->getArray('logger'));
    },

    BasePathMiddleware::class => function (ContainerInterface $container) {
        $app = $container->get(App::class);

        return new BasePathMiddleware($app);
    },

    EntityManager::class => DoctrineConnector::getEntityManager(),

    // And add this entry
    JwtAuth::class => function (ContainerInterface $container) {
        $config = $container->get(Configuration::class);

        $issuer = $config->getString('jwt.issuer');
        $clientId = $config->getString('jwt.client-id');
        $lifetime = $config->getInt('jwt.lifetime');
        $privateKeyFile = $config->getString('jwt.private_key_file');
        $publicKeyFile = $config->getString('jwt.public_key_file');
        $secretPhrase = $config->getString('app.secret');

        $jwtConfig = Lcobucci\JWT\Configuration::forAsymmetricSigner(
            new Signer\Rsa\Sha256(),
            LocalFileReference::file($privateKeyFile),
            InMemory::base64Encoded($secretPhrase)
        );

        $jwtConfig->setValidationConstraints(
            new IssuedBy($issuer),
            new PermittedFor($clientId),
            new SignedWith(new Signer\Rsa\Sha256(), LocalFileReference::file($publicKeyFile)),
            new StrictValidAt(SystemClock::fromSystemTimezone()),
        );

        return new JwtAuth($jwtConfig, $issuer, $clientId, $lifetime);
    },
];
