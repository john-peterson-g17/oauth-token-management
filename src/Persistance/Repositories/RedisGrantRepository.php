<?php

namespace JohnPetersonG17\JwtAuthentication\Persistance\Repositories;

use JohnPetersonG17\JwtAuthentication\Exceptions\NotFoundException;
use JohnPetersonG17\JwtAuthentication\Grant;
use Predis\Client;

class RedisGrantRepository implements GrantRepository {

    // TODO: Support to configure the prefix

    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function save(Grant $grant): void
    {
        $this->client->set($grant->userId(), $grant);
    }

    public function find(mixed $userId): Grant
    {
        $rawGrant = $this->client->get($userId);

        if(!isset($rawGrant) || empty($rawGrant)) {
            throw new NotFoundException("Grant for user $userId not found");
        }

        $grantArray = json_decode($rawGrant, true);

        return new Grant(
            $grantArray['user_id'], 
            $grantArray['access_token'], 
            $grantArray['refresh_token'], 
            $grantArray['expires_in']
        );
    }

    public function delete(mixed $userId): void
    {
        // Predis has no delete command so we set the entry to null
        $this->client->set($userId, null);
    }
}