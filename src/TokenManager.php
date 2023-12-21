<?php

namespace JohnPetersonG17\JwtAuthentication;

// TODO: Better name for this
class TokenManager {

    private Config $config;
    private TokenFactory $factory;

    public function __construct(Config $config) {
        $this->config = $config;

        // Setup needed classes
        // TODO: Setup persistance driver/repository
        // TODO Setup predis client and inject
        $this->repository = new RedisTokenRepository();
        // TODO: Setup factory
        $this->factory = new TokenFactory($this->config);
    }

    /**
     * Grants an Access and Refresh token to a user who has successfully authenticated
     * @param mixed $userId
     * @return Grant
     */
    public function grant(mixed $userId): Grant
    {
        // Create the tokens
        $accessToken = $this->factory->make($userId, TokenType::ACCESS);
        $refreshToken = $this->factory->make($userId, TokenType::REFRESH);

        // TODO: Only persist if the config states a persistance driver is used
        // Persist the tokens
        $this->repository->save($accessToken);
    }

    /**
     * Revokes a users Access and Refresh tokens
     * @param mixed $userId
     * @return void
     */
    public function revoke(mixed $userId): void
    {

    }

    /**
     * Refreshes a users Access token
     * @param string $refreshToken The raw string value of the refresh token
     * @throws TokenNotFoundException if the refresh token is not found
     * @throws TokenExpiredException if token is expired
     * @return Token The new access token
     */
    public function refresh(string $refreshToken): Token
    {

    }

    // TODO: Implement these quality of life methods
    // /**
    //  * Retrieves a users Access token
    //  */
    // public function accessToken(mixed $userId): Token
    // {

    // }

    // /**
    //  * Retrieves a users Refresh token
    //  */
    // public function refreshToken(mixed $userId): Token
    // {

    // }
}