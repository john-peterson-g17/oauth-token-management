<?php

namespace JohnPetersonG17\OAuthTokenManagement\Tests\Integration;

use JohnPetersonG17\OAuthTokenManagement\AuthorizationGate;
use JohnPetersonG17\OAuthTokenManagement\Grant;
use JohnPetersonG17\OAuthTokenManagement\Config;
use JohnPetersonG17\OAuthTokenManagement\Persistance\Driver;
use JohnPetersonG17\OAuthTokenManagement\Exceptions\NotFoundException;
use JohnPetersonG17\OAuthTokenManagement\Exceptions\PersistanceDriverNotSetException;
use JohnPetersonG17\OAuthTokenManagement\Exceptions\TokenExpiredException;
use JohnPetersonG17\OAuthTokenManagement\Tests\Helpers\LoadsEnvironmentVariables;
use PHPUnit\Framework\TestCase;

class AuthorizationGateTest extends TestCase {

    use LoadsEnvironmentVariables;

    private AuthorizationGate $gate;
    private $host;
    private $port;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadEnvironmentVariables();

        $this->host = getenv('REDIS_HOST');
        $this->port = getenv('REDIS_PORT');

        $this->gate = new AuthorizationGate(new Config());
    }

    /**
     * @test
     */
    public function it_can_grant_tokens()
    {
        $config = new Config(['persistance_driver' => Driver::None]);
        $this->gate->setConfig($config);

        $grant = $this->gate->grant(1);

        $this->assertInstanceOf(Grant::class, $grant);
        $this->assertEquals(1, $grant->userId());
        $this->assertIsString($grant->accessToken());
        $this->assertIsString($grant->refreshToken());
        $this->assertIsInt($grant->expiresIn());
        $this->assertEquals(Config::DEFAULT_ACCESS_TOKEN_EXPIRATION, $grant->expiresIn());
        $this->assertEquals('Bearer', $grant->tokenType());
    }

    /**
     * @test
     */
    public function it_can_retrieve_tokens_when_a_redis_persistance_driver_is_used()
    {
        $config = new Config([
            'persistance_driver' => Driver::Redis,
            'redis' => [
                'parameters' => [
                    'host' => $this->host,
                    'port' => $this->port,
                ]
            ]
        ]);
        $this->gate->setConfig($config);
        $this->gate->grant(1); // Create a grant as a part of setup

        $grant = $this->gate->retrieve(1);

        $this->assertInstanceOf(Grant::class, $grant);
        $this->assertEquals(1, $grant->userId());
        $this->assertIsString($grant->accessToken());
        $this->assertIsString($grant->refreshToken());
        $this->assertIsInt($grant->expiresIn());
        $this->assertEquals(Config::DEFAULT_ACCESS_TOKEN_EXPIRATION, $grant->expiresIn());
        $this->assertEquals('Bearer', $grant->tokenType());
    }

    /**
     * @test
     */
    public function it_can_revoke_tokens_when_a_redis_persistance_driver_is_used()
    {
        $config = new Config([
            'persistance_driver' => Driver::Redis,
            'redis' => [
                'parameters' => [
                    'host' => $this->host,
                    'port' => $this->port,
                ]
            ]
        ]);
        $this->gate->setConfig($config);
        $this->gate->grant(1); // Create a grant as a part of setup

        $this->gate->revoke(1);

        // Should throw a not found exception because we revoked the grant
        $this->expectException(NotFoundException::class);
        $this->gate->retrieve(1);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_attempting_to_retrieve_a_grant_when_no_persistance_driver_is_set()
    {
        $config = new Config(['persistance_driver' => Driver::None]);
        $this->gate->setConfig($config);
        $this->gate->grant(1); // Create a grant as a part of setup

        $this->expectException(PersistanceDriverNotSetException::class);
        $this->gate->retrieve(1);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_attempting_to_revoke_a_grant_when_no_persistance_driver_is_set()
    {
        $config = new Config(['persistance_driver' => Driver::None]);
        $this->gate->setConfig($config);
        $this->gate->grant(1); // Create a grant as a part of setup

        $this->expectException(PersistanceDriverNotSetException::class);
        $this->gate->revoke(1);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_attempting_to_retrieve_a_grant_that_does_not_exist()
    {
        $config = new Config([
            'persistance_driver' => Driver::Redis,
            'redis' => [
                'parameters' => [
                    'host' => $this->host,
                    'port' => $this->port,
                ]
            ]
        ]);
        $this->gate->setConfig($config);
        $this->gate->revoke(1); // Ensure that there is not a grant for user ID 1
        // Do not issue a grant for a user ID of 1

        $this->expectException(NotFoundException::class);
        $this->gate->retrieve(1);
    }

    /**
     * @test
     */
    public function it_can_refresh_an_access_token_when_given_a_valid_refresh_token()
    {
        $config = new Config([
            'persistance_driver' => Driver::Redis,
            'redis' => [
                'parameters' => [
                    'host' => $this->host,
                    'port' => $this->port,
                ]
            ],
        ]);
        $this->gate->setConfig($config);
        $grant = $this->gate->grant(1); // Create a grant as a part of setup

        $newGrant = $this->gate->refresh($grant->refreshToken());

        $this->assertInstanceOf(Grant::class, $newGrant);
        $this->assertEquals(1, $newGrant->userId());
        $this->assertIsString($newGrant->accessToken());
        $this->assertNotEquals($grant->accessToken(), $newGrant->accessToken()); // Ensure that the access token is different
        $this->assertIsString($newGrant->refreshToken());
        $this->assertEquals($grant->refreshToken(), $newGrant->refreshToken()); // Ensure that the refresh token is the same
        $this->assertIsInt($newGrant->expiresIn());
        $this->assertEquals(Config::DEFAULT_ACCESS_TOKEN_EXPIRATION, $newGrant->expiresIn());
        $this->assertEquals('Bearer', $newGrant->tokenType());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_attempting_to_refresh_an_access_token_when_no_persistance_driver_is_set()
    {
        $config = new Config(['persistance_driver' => Driver::None]);
        $this->gate->setConfig($config);
        $grant = $this->gate->grant(1); // Create a grant as a part of setup

        $this->expectException(PersistanceDriverNotSetException::class);
        $this->gate->refresh($grant->refreshToken());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_attempting_to_refresh_an_access_token_when_the_refresh_token_is_expired()
    {
        $config = new Config([
            'persistance_driver' => Driver::Redis,
            'redis' => [
                'parameters' => [
                    'host' => $this->host,
                    'port' => $this->port,
                ]
            ],
            'refresh_token_expiration' => 1,
        ]);
        $this->gate->setConfig($config);
        $grant = $this->gate->grant(1); // Create a grant as a part of setup

        sleep(2); // Sleep for 2 seconds to ensure that the refresh token is expired

        $this->expectException(TokenExpiredException::class);
        $this->gate->refresh($grant->refreshToken());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_attempting_to_refresh_an_access_token_for_a_user_that_does_not_have_a_grant()
    {
        $config = new Config([
            'persistance_driver' => Driver::Redis,
            'redis' => [
                'parameters' => [
                    'host' => $this->host,
                    'port' => $this->port,
                ]
            ],
        ]);
        $this->gate->setConfig($config);
        $grant = $this->gate->grant(1); // Create a grant as a part of setup
        $this->gate->revoke(1); // Ensure that there is not a grant for user ID 1
        // Do not issue a grant for a user ID of 1

        $this->expectException(NotFoundException::class);
        $this->gate->refresh($grant->refreshToken());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_attempting_to_authorize_and_the_access_token_is_expired()
    {
        $config = new Config([
            'access_token_expiration' => 1,
        ]);
        $this->gate->setConfig($config);
        $grant = $this->gate->grant(1); // Create a grant as a part of setup

        sleep(2); // Sleep for 2 seconds to ensure that the access token is expired

        $this->expectException(TokenExpiredException::class);
        $this->gate->authorize($grant->accessToken());
    }
}