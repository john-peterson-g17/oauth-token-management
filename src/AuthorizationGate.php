<?php

namespace JohnPetersonG17\JwtAuthentication;

use JohnPetersonG17\JwtAuthentication\Codecs\FirebaseJWTCodec;
use JohnPetersonG17\JwtAuthentication\Grant;
use JohnPetersonG17\JwtAuthentication\Token\TokenPurpose;
use JohnPetersonG17\JwtAuthentication\Token\TokenFactory;
use JohnPetersonG17\JwtAuthentication\Exceptions\TokenExpiredException;
use JohnPetersonG17\JwtAuthentication\Exceptions\NotFoundException;
use JohnPetersonG17\JwtAuthentication\Codecs\Codec;
use JohnPetersonG17\JwtAuthentication\Exceptions\PersistanceDriverNotSetException;
use JohnPetersonG17\JwtAuthentication\Persistance\Driver;
use JohnPetersonG17\JwtAuthentication\Persistance\Repositories\GrantRepository;
use JohnPetersonG17\JwtAuthentication\Persistance\Repositories\RedisGrantRepository;
use Predis\Client;

class AuthorizationGate {

    private Config $config;
    private TokenFactory $factory;
    private FirebaseJWTCodec $codec;
    private GrantRepository $repository;

    public function __construct(Config $config) {
        $this->config = $config;

        $this->setupClasses();
    }

    private function setupClasses(): void
    {
        $this->setupTokenFactory();
        $this->setupCodec();

        if ($this->config->get('persistance_driver') !== Driver::None) {
            $this->setupRepository();
        }
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

    private function setupRepository(): void
    {
        // TODO: Potentially move this to a factory class
        if ($this->config->get('persistance_driver') === Driver::Redis) {
            $this->repository = new RedisGrantRepository(
                new Client($this->config->get('redis.parameters'), $this->config->get('redis.options')),
            );
        }
        // TODO: Other persistance drivers
    }

    /**
     * Sets the Codec to be used for encoding and decoding tokens
     * This method allows additional flexability for implementing custom Codecs
     * @param Codec $codec
     * @return void
     */
    public function setCodec(Codec $codec): void
    {
        $this->codec = $codec;
    }

    /**
     * Sets the Config to be used for the AuthorizationGate
     * This method allows additional flexability to swap configs after creating the Authorization Gate
     * @param Config $config
     * @return void
     */
    public function setConfig(Config $config): void
    {
        $this->config = $config;
        $this->setupClasses(); // Re-setup classes with new config
    }

    /**
     * Grants an Access and Refresh token to a user who has successfully authenticated
     * @param mixed $userId
     * @return Grant
     */
    public function grant(mixed $userId): Grant
    {
        // Create the tokens
        $accessToken = $this->factory->make($userId, TokenPurpose::ACCESS);
        $refreshToken = $this->factory->make($userId, TokenPurpose::REFRESH);

        $grant = new Grant(
            $userId,
            $this->codec->encode($accessToken),
            $this->codec->encode($refreshToken),
            $this->config->get('access_token_expiration'),
        );

        // Persist the tokens if using a persistance driver
        if($this->config->get('persistance_driver') !== Driver::None) {
            $this->repository->save($grant);
        }

        return $grant;
    }

    /**
     * Revokes a users Access and Refresh tokens (Grant)
     * @param mixed $userId
     * @return void
     */
    public function revoke(mixed $userId): void
    {
        if ($this->config->get('persistance_driver') !== Driver::None) {
            // Delete the grant
            $this->repository->delete($userId);
            return;
        }

        throw new PersistanceDriverNotSetException('Persistance driver is not set. Unable to revoke grant.');
    }

    /**
     * Retrieves a users Access and Refresh tokens (Grant)
     * @param mixed $userId
     * @throws NotFoundException if the grant is not found
     * @throws PersistanceDriverNotSetException if the persistance driver is not set
     * @return Grant
     */
    public function retrieve(mixed $userId): Grant
    {
        // Retrieve the grant
        if($this->config->get('persistance_driver') !== Driver::None) {
            return $this->repository->find($userId); // Throws NotFoundException if not found
        }

        throw new PersistanceDriverNotSetException('Persistance driver is not set. Unable to retrieve grant.');
    }

    /**
     * Refreshes a users Access token
     * @param string $refreshToken The raw string value of the refresh token
     * @throws TokenNotFoundException if the refresh token is not found
     * @throws TokenExpiredException if token is expired
     * @return Grant a grant with the new access token
     */
    public function refresh(string $refreshToken): Grant
    {
        if ($this->config->get('persistance_driver') === Driver::None) {
            throw new PersistanceDriverNotSetException('Persistance driver is not set. Unable to refresh grant.');
        }

        // Check if the refresh token is expired
        $decodedRefreshToken = $this->factory->makeFromRaw($this->codec->decode($refreshToken));
        if ($decodedRefreshToken->isExpired()) {
            throw new TokenExpiredException('Refresh token is expired');
        }

        // TODO: Finish testing this logic
        
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

        $this->repository->save($grant);
        
        return $grant;
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