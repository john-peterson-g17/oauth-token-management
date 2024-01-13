<?php

namespace JohnPetersonG17\OAuthTokenManagement\Token;

use Ramsey\Uuid\Uuid;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;

class TokenFactory
{
    private mixed $userId;
    private TokenPurpose $purpose;
    private DateTimeImmutable $expiresAt;
    private DateTimeImmutable $issuedAt;

    private string $issuer;
    private int $accessTokenExpiration;
    private int $refreshTokenExpiration;

    public function __construct(string $issuer, int $accessTokenExpiration, int $refreshTokenExpiration)
    {
        $this->accessTokenExpiration = $accessTokenExpiration;
        $this->refreshTokenExpiration = $refreshTokenExpiration;
        $this->issuer = $issuer;
    }

    /**
     * Create a Token
     * @param mixed $userId
     * @param TokenPurpose $purpose
     * @return Token
     */
    public function make(mixed $userId, TokenPurpose $purpose): Token
    {
        $this->userId = $userId;
        $this->purpose = $purpose;

        $this->calculateissuedAt();
        $this->calculateExpiresAt();

        return $this->createToken();
    }

    private function calculateIssuedAt(): void
    {
        $this->issuedAt = new DateTimeImmutable('now', new DateTimeZone('utc'));
    }

    private function calculateExpiresAt(): void
    {
        $interval = null;
        if ($this->purpose == TokenPurpose::ACCESS) {
            $interval = DateInterval::createFromDateString($this->accessTokenExpiration . ' seconds');
        } else { // Refresh Token
            $interval = DateInterval::createFromDateString($this->refreshTokenExpiration . ' seconds');
        }

        $this->expiresAt = $this->issuedAt->add($interval);
    }

    private function createToken(): Token
    {
        return new Token(
            Uuid::uuid4()->toString(),
            $this->issuer,
            $this->userId,
            $this->purpose,
            $this->issuedAt,
            $this->expiresAt
        );
    }

    /**
     * Create a Token from a raw array of claims
     * @param array $raw
     * @return Token
     */
    public function makeFromRaw(array $raw): Token
    {
        $this->validateRawData($raw);

        return new Token(
            $raw['jti'],
            $raw['iss'],
            $raw['sub'],
            TokenPurpose::from($raw['prp']),
            (new DateTimeImmutable())->setTimestamp($raw['iat']),
            (new DateTimeImmutable())->setTimestamp($raw['exp'])
        );
    }

    private function validateRawData(array $raw): void
    {
        $this->validateJtiField($raw['jti'] ?? null);
        $this->validateIssField($raw['iss'] ?? null);
        $this->validateSubField($raw['sub'] ?? null);
        $this->validatePrpField($raw['prp'] ?? null);
        $this->validateIatField($raw['iat'] ?? null);
        $this->validateExpField($raw['exp'] ?? null);
    }

    private function validateJtiField($jti): void
    {
        
        if (!isset($jti)) {
            throw new \InvalidArgumentException('Raw array requires an "jti" field in order to create a Token');
        }

        if (empty($jti)) {
            throw new \InvalidArgumentException('Raw array field "jti" must not be empty');
        }
    }

    private function validateIssField($iss): void
    {
        if (!isset($iss)) {
            throw new \InvalidArgumentException('Raw array requires an "iss" field in order to create a Token');
        }

        if (empty($iss)) {
            throw new \InvalidArgumentException('Raw array field "iss" must not be empty');
        }

        if (!is_string($iss)) {
            throw new \InvalidArgumentException('Raw array field "iss" must be a string');
        }
    }

    private function validateSubField($sub): void
    {
        if (!isset($sub)) {
            throw new \InvalidArgumentException('Raw array requires an "sub" field in order to create a Token');
        }

        if (empty($sub)) {
            throw new \InvalidArgumentException('Raw array field "sub" must not be empty');
        }
    }

    private function validatePrpField($prp): void
    {
        if (!isset($prp)) {
            throw new \InvalidArgumentException('Raw array requires an "prp" field in order to create a Token');
        }

        if (empty($prp)) {
            throw new \InvalidArgumentException('Raw array field "prp" must not be empty');
        }

        if (!is_string($prp)) {
            throw new \InvalidArgumentException('Raw array field "prp" must be an string');
        }

        if(TokenPurpose::tryFrom($prp) == null) {
            throw new \InvalidArgumentException('Raw array field "prp" must be a valid TokenPurpose');
        }
    }

    private function validateIatField($iat): void
    {
        if (!isset($iat)) {
            throw new \InvalidArgumentException('Raw array requires an "iat" field in order to create a Token');
        }

        if (!is_int($iat)) {
            throw new \InvalidArgumentException('Raw array field "iat" must be an integer representing a Unix Timestamp');
        }
    }

    private function validateExpField($exp): void
    {
        if (!isset($exp)) {
            throw new \InvalidArgumentException('Raw array requires an "exp" field in order to create a Token');
        }

        if (!is_int($exp)) {
            throw new \InvalidArgumentException('Raw array field "exp" must be an integer representing a Unix Timestamp');
        }
    }

    /**
     * Get the date time that the most recent token was issued at
     * @return DateTimeImmutable
     */
    public function getMostRecentIssuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    // The setters below purposefully do not validate values as they are set by the Config class which validates them
    // Testing is the only time that we would set values that do not come directly from config class
    // We also only expect this class to be used internally so we can trust that the values are valid

    /**
     * Set the issuer for tokens created by this factory
     * @param string $issuer
     */
    public function setIssuer(string $issuer): void
    {
        $this->issuer = $issuer;
    }

    /**
     * Set the access token expiration in seconds for tokens created by this factory
     * @param int $accessTokenExpiration
     */
    public function setAccessTokenExpiration(int $accessTokenExpiration): void
    {
        $this->accessTokenExpiration = $accessTokenExpiration;
    }

    /**
     * Set the refresh token expiration in seconds for tokens created by this factory
     * @param int $refreshTokenExpiration
     */
    public function setRefreshTokenExpiration(int $refreshTokenExpiration): void
    {
        $this->refreshTokenExpiration = $refreshTokenExpiration;
    }
}