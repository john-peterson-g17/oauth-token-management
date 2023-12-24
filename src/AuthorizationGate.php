<?php

namespace JohnPetersonG17\JwtAuthentication;

use JohnPetersonG17\JwtAuthentication\Codecs\FirebaseJWTCodec;
use JohnPetersonG17\JwtAuthentication\Grant;
use JohnPetersonG17\JwtAuthentication\Token\TokenPurpose;
use JohnPetersonG17\JwtAuthentication\Token\TokenFactory;
use JohnPetersonG17\JwtAuthentication\Token\Exceptions\TokenExpiredException;
use JohnPetersonG17\JwtAuthentication\Persistance\Exceptions\NotFoundException;

class AuthorizationGate {

    private Config $config;
    private TokenFactory $factory;
    private FirebaseJWTCodec $codec;

    public function __construct(Config $config) {
        $this->config = $config;

        // Setup needed classes
        $this->setupTokenFactory();
        $this->setupCodec();
    }

    private function setupTokenFactory(): void
    {
        // Config defaults enusre that these values are always set
        $this->factory = new TokenFactory(
            $this->config->get('issuer'),
            $this->config->get('access_token_expiration'),
            $this->config->get('refresh_token_expiration')
        );
    }

    private function setupCodec(): void
    {
        // Config defaults enusre that these values are always set
        $this->codec = new FirebaseJWTCodec(
            $this->config->get('key'),
            $this->config->get('hashing_algorithm')
        );
    }

    /**
     * Grants an Access and Refresh token to a user who has successfully authenticated
     * @param mixed $userId
     * @return Grant
     */
    public function grant(mixed $userId): Grant
    {
        // Check if a grant for the user already exists
        // Throws a NotFoundException if not found

        // Create the tokens
        $accessToken = $this->factory->make($userId, TokenPurpose::ACCESS);
        $refreshToken = $this->factory->make($userId, TokenPurpose::REFRESH);

        // TODO: Only persist if the config states a persistance driver is used
        // Persist the tokens

        return new Grant(
            $userId,
            $this->codec->encode($accessToken),
            $this->codec->encode($refreshToken),
            $this->config->get('access_token_expiration'),
        );
    }

    /**
     * Revokes a users Access and Refresh tokens (Grant)
     * @param mixed $userId
     * @return void
     */
    public function revoke(mixed $userId): void
    {
        // Delete the grant
        $this->repository->delete($userId);
    }

    /**
     * Refreshes a users Access token
     * @param string $refreshToken The raw string value of the refresh token
     * @throws TokenNotFoundException if the refresh token is not found
     * @throws TokenExpiredException if token is expired
     * @return string The new access token
     */
    public function refresh(string $refreshToken): string
    {
        // Check if the refresh token is expired
        $decodedRefreshToken = $this->factory->makeFromRaw($this->codec->decode($refreshToken));
        if ($decodedRefreshToken->isExpired()) {
            throw new TokenExpiredException('Refresh token is expired');
        }

        // Check if a grant for the user exists
        // Throws a NotFoundException if not found
        $userId = $decodedRefreshToken->subject();
        $this->repository->find($userId);

        // Create a new access token
        $accessToken = $this->factory->make($userId, TokenPurpose::ACCESS);

        // Create a new grant with the new access token
        $grant = new Grant(
            $userId,
            $this->codec->encode($accessToken),
            $refreshToken, // Use the same refresh token
            $this->config->get('access_token_expiration'), // Use the config value for the expiration which is "reset"
        );

        // Persist the grant
        $this->repository->save($grant);
    }

    /**
     * Authorizes a user based on their access token
     * @param string $accessToken The raw string value of the access token
     * @return bool
     */
    public function authorize(string $accessToken): bool
    {
        // Check if the access token is expired
        $decodedAccessToken = $this->factory->makeFromRaw($this->codec->decode($accessToken));
        if ($decodedAccessToken->isExpired()) {
            return false; // TODO: should we throw exceptions instead?
        }

        // Check if a grant for the user exists
        // Throws a NotFoundException if not found
        $userId = $decodedAccessToken->subject();
        try {
            $this->repository->find($userId);
        } catch (NotFoundException $e) {
            return false;
        }

        return true;
    }
}