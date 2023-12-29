<?php

namespace JohnPetersonG17\JwtAuthentication\Tests\Integration;

use JohnPetersonG17\JwtAuthentication\Persistance\Repositories\RedisGrantRepository;
use Predis\Client;
use Dotenv;
use JohnPetersonG17\JwtAuthentication\Grant;
use JohnPetersonG17\JwtAuthentication\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;

class RedisGrantRepositoryTest extends TestCase {

    private Client $client;
    private RedisGrantRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $dotenv = Dotenv\Dotenv::createMutable(__DIR__ . '/../../');
        $dotenv->load();

        $host = $_ENV['REDIS_HOST'];
        $port = $_ENV['REDIS_PORT'];

        $this->client = new Client([
            'host' => $host,
            'port' => $port,
        ]);

        $this->repository = new RedisGrantRepository($this->client);
    }


    /**
     * @test
     */
    public function it_can_save_a_grant()
    {
        $grant = new Grant(1, 'someAccessToken', 'someRefreshToken', 60);

        $this->repository->save($grant);

        // Check to see if it saved the grant
        $rawGrant = $this->client->get(1);

        $rawGrant = json_decode($rawGrant, true);

        $this->assertEquals($grant->userId(), $rawGrant['user_id']);
        $this->assertEquals($grant->accessToken(), $rawGrant['access_token']);
        $this->assertEquals($grant->refreshToken(), $rawGrant['refresh_token']);
        $this->assertEquals($grant->expiresIn(), $rawGrant['expires_in']);
    }

    /**
     * @test
     */
    public function it_can_find_a_grant()
    {
        $expectedGrant = new Grant(1, 'someAccessToken', 'someRefreshToken', 60);

        // Put the grant in redis and see if it can find it
        $this->client->set(1, $expectedGrant);
        
        $grant = $this->repository->find(1);

        $this->assertInstanceOf(Grant::class, $grant);
        $this->assertEquals($expectedGrant, $grant);
    }

    /**
     * @test
     */
    public function it_throws_a_not_found_exception_if_if_cannot_find_a_grant()
    {
        // Ensure that the grant is not set
        $this->client->set(1, null);
        
        $this->expectException(NotFoundException::class);

        $this->repository->find(1);
    }

    /**
     * @test
     */
    public function it_can_delete_a_grant()
    {
        $expectedGrant = new Grant(1, 'someAccessToken', 'someRefreshToken', 60);

        // Put the grant in redis and see if it can delete it
        $this->client->set(1, $expectedGrant);
        
        $this->repository->delete(1);

        $found = $this->client->get(1);

        $this->assertTrue(empty($found) || !isset($found));
    }
}