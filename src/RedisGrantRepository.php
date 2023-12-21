<?php

namespace JohnPetersonG17\JwtAuthentication;

use Predis\Client;

class RedisGrantRepository implements GrantRepository {

    // TODO: Support to configure the prefix
    private const PREFIX = 'jwt-auth:';

    private Client $client;
    private TokenFactory $tokenFactory;

    public function __construct(Client $client, TokenFactory $tokenFactory)
    {
        $this->client = $client;
        $this->tokenFactory = $tokenFactory;
    }

    public function save(Grant $grant): void
    {
        $key = $this->createKey($grant->userId());
        $this->client->set($key, $grant->_toString());
    }

    // TODO: Be able to look up the access token based on the refresh token associated with it
    public function find(mixed $userId): Grant
    {
        $key = $this->createKey($userId);
        $rawGrant = $this->client->get($key);

        if(!isset($rawGrant)) {
            throw new NotFoundException("Grant for user $userId not found");
        }

        $grantArray = json_decode($rawGrant, true);

        $accessToken = $this->tokenFactory->fromValue($grantArray['access_token']);
        $refreshToken = $this->tokenFactory->fromValue($grantArray['refresh_token']);

        return new Grant($userId, $accessToken, $refreshToken);
    }

    public function delete(Token $token): void
    {
        $key = $this->createKey($token->userId(), $token->type());
        $this->client->delete($key);
    }

    private function createKey(mixed $userId): string
    {
        return self::PREFIX . $userId;
    }
}