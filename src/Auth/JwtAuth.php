<?php

/**
 * src/Auth/JwtAuth.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Auth;

use DateTimeImmutable;
use InvalidArgumentException;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Plain;
use Ramsey\Uuid\Uuid;
use TDW\ACiencia\Entity\Role;
use TDW\ACiencia\Entity\User;

/**
 * Class JwtAuth
 */
final class JwtAuth
{
    private Configuration $config;
    private string $issuer;

    // OAuth2 client id.
    private string $clientId;

    // Max lifetime in seconds
    private int $lifetime;

    /**
     * The constructor.
     *
     * @param Configuration $config
     * @param string $issuer
     * @param string $clientId OAuth2 client id.
     * @param int $lifetime The max lifetime
     */
    public function __construct(
        Configuration $config,
        string $issuer,
        string $clientId,
        int $lifetime
    ) {
        $this->config = $config;
        $this->issuer = $issuer;
        $this->clientId = $clientId;
        $this->lifetime = $lifetime;
    }

    /**
     * Get JWT max lifetime.
     *
     * @return int The lifetime in seconds
     */
    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    /**
     * Create JSON web token.
     *
     * @param User $user
     * @param array $requestedScopes Requested scopes
     * @return Plain The JWT
     */
    public function createJwt(User $user, array $requestedScopes = Role::ROLES): Plain
    {
        $awardedScopes = array_filter(
            array_unique(array_merge($requestedScopes, [Role::ROLE_READER])),
            fn($role) => $user->hasRole($role),
        );

        $now = new DateTimeImmutable();

        $token = $this->config->builder()
            ->issuedBy($this->issuer)   // iss: Issuer (who created and signed this token)
            ->issuedAt($now)    // iat: The time at which the JWT was issued
            ->relatedTo($user->getUsername()) // sub: Subject (whom de token refers to)
            ->identifiedBy(Uuid::uuid4()->toString())   // jti: JWT id (unique identifier for this token)
            ->canOnlyBeUsedAfter($now)  // nbf: Not valid before
            ->expiresAt($now->modify('+' . $this->lifetime . ' seconds'))
            ->permittedFor($this->clientId) // Audience (who or what the token is intended for)
            ->withClaim('uid', $user->getId())
            ->withClaim('scopes', array_values($awardedScopes))
            ->getToken($this->config->signer(), $this->config->signingKey());

        return $token;
    }

    /**
     * Parse token.
     *
     * @param string $token The JWT
     *
     * @return Token The parsed token
     */
    public function createParsedToken(string $token): Token
    {
        return $this->config->parser()->parse($token);
    }

    /**
     * Validate the access token.
     *
     * @param string $accessToken The JWT
     *
     * @return bool The status
     */
    public function validateToken(string $accessToken): bool
    {
        assert($this->config instanceof Configuration);

        $token = $this->config->parser()->parse($accessToken);
        assert($token instanceof Plain);

        if (! $this->config->validator()->validate($token, ...$this->config->validationConstraints())) {
            throw new InvalidArgumentException('Invalid token provided');
        }

        return true;
    }
}
