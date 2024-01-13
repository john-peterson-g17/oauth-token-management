<?php

namespace JohnPetersonG17\OAuthTokenManagement\Tests\Unit;

use JohnPetersonG17\OAuthTokenManagement\Config;
use JohnPetersonG17\OAuthTokenManagement\HashingAlgorithm;
use JohnPetersonG17\OAuthTokenManagement\Persistance\Driver;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase {

    /**
     * @test
     */
    public function it_returns_expected_defaults_when_no_values_are_set()
    {
        $config = new Config();

        $this->assertEquals(Config::DEFAULT_ISSUER, $config->get('issuer'));
        $this->assertEquals(Config::DEFAULT_KEY, $config->get('key'));
        $this->assertEquals(Config::DEFAULT_ACCESS_TOKEN_EXPIRATION, $config->get('access_token_expiration'));
        $this->assertEquals(Config::DEFAULT_REFRESH_TOKEN_EXPIRATION, $config->get('refresh_token_expiration'));
        $this->assertEquals(Config::DEFAULT_HASHING_ALGORITHM, $config->get('hashing_algorithm'));
        $this->assertEquals(Config::DEFAULT_PERSISTANCE_DRIVER, $config->get('persistance_driver'));

        // These should not be defined for the default persistance driver which is not redis
        $this->assertFalse($config->has('redis.parameters'));
        $this->assertFalse($config->has('redis.options'));
    }

    /**
     * @test
     */
    public function it_returns_expected_redis_defaults_when_redis_persistance_driver_is_set()
    {
        $config = new Config(
            ['persistance_driver' => Driver::Redis]
        );

        $this->assertEquals(Driver::Redis, $config->get('persistance_driver'));
        $this->assertEquals(Config::DEFAULT_REDIS_PARAMETERS, $config->get('redis.parameters'));
        $this->assertEquals(Config::DEFAULT_REDIS_OPTIONS, $config->get('redis.options'));
    }

    /**
     * @test
     */
    public function it_can_set_and_get_a_valid_issuer()
    {
        $expected = 'someIssuer';

        $config = new Config(
            ['issuer' => 'someIssuer']
        );

        $this->assertEquals($expected, $config->get('issuer'));
    }

    /**
     * @test
     * @dataProvider invalidIssuerProvider
     */
    public function it_throws_an_exception_when_setting_an_invalid_issuer($issuer)
    {
        $this->expectException(\InvalidArgumentException::class);

        new Config(
            ['issuer' => $issuer]
        );
    }

    public static function invalidIssuerProvider()
    {
        return [
            'issuer_is_empty string' => [
                'issuer' => ''
            ],
            'issuer_is_array' => [
                'issuer' => []
            ],
            'issuer_is_object' => [
                'issuer' => new \stdClass()
            ],
            'issuer_is_boolean' => [
                'issuer' => true
            ],
            'issuer_is_integer' => [
                'issuer' => 1
            ],
            'issuer_is_float' => [
                'issuer' => 1.1
            ],
        ];
    }

    /**
     * @test
     */
    public function it_can_set_and_get_a_valid_key()
    {
        $expected = 'someKey';

        $config = new Config(
            ['key' => 'someKey']
        );

        $this->assertEquals($expected, $config->get('key'));
    }

    /**
     * @test
     * @dataProvider invalidKeyProvider
     */
    public function it_throws_an_exception_when_setting_an_invalid_key($key)
    {
        $this->expectException(\InvalidArgumentException::class);

        new Config(
            ['key' => $key]
        );
    }

    public static function invalidKeyProvider()
    {
        return [
            'key_is_empty string' => [
                'key' => ''
            ],
            'key_is_array' => [
                'key' => []
            ],
            'key_is_object' => [
                'key' => new \stdClass()
            ],
            'key_is_boolean' => [
                'key' => true
            ],
            'key_is_integer' => [
                'key' => 1
            ],
            'key_is_float' => [
                'key' => 1.1
            ],
        ];
    }

    /**
     * @test
     */
    public function it_can_set_and_get_a_valid_access_token_expiration()
    {
        $expected = 1;

        $config = new Config(
            ['access_token_expiration' => 1]
        );

        $this->assertEquals($expected, $config->get('access_token_expiration'));
    }

    /**
     * @test
     * @dataProvider invalidAccessTokenExpirationProvider
     */
    public function it_throws_an_exception_when_setting_an_invalid_access_token_expiration($access_token_expiration)
    {
        $this->expectException(\InvalidArgumentException::class);

        new Config(
            ['access_token_expiration' => $access_token_expiration]
        );
    }

    public static function invalidAccessTokenExpirationProvider()
    {
        return [
            'access_token_expiration_is_empty string' => [
                'access_token_expiration' => ''
            ],
            'access_token_expiration_is_array' => [
                'access_token_expiration' => []
            ],
            'access_token_expiration_is_object' => [
                'access_token_expiration' => new \stdClass()
            ],
            'access_token_expiration_is_boolean' => [
                'access_token_expiration' => true
            ],
            'access_token_expiration_is_string' => [
                'access_token_expiration' => '1'
            ],
            'access_token_expiration_is_float' => [
                'access_token_expiration' => 1.1
            ],
            'access_token_expiration_is_negative' => [
                'access_token_expiration' => -1
            ],
            'access_token_expiration_is_zero' => [
                'access_token_expiration' => 0
            ],
        ];
    }

    /**
     * @test
     * @dataProvider invalidRefreshTokenExpirationProvider
     */
    public function it_can_set_and_get_a_valid_refresh_token_expiration()
    {
        $expected = 1;

        $config = new Config(
            ['refresh_token_expiration' => 1]
        );

        $this->assertEquals($expected, $config->get('refresh_token_expiration'));
    }

    /**
     * @test
     * @dataProvider invalidRefreshTokenExpirationProvider
     */
    public function it_throws_an_exception_when_setting_an_invalid_refresh_token_expiration($refresh_token_expiration)
    {
        $this->expectException(\InvalidArgumentException::class);

        new Config(
            ['refresh_token_expiration' => $refresh_token_expiration]
        );
    }

    public static function invalidRefreshTokenExpirationProvider()
    {
        return [
            'refresh_token_expiration_is_empty string' => [
                'refresh_token_expiration' => ''
            ],
            'refresh_token_expiration_is_array' => [
                'refresh_token_expiration' => []
            ],
            'refresh_token_expiration_is_object' => [
                'refresh_token_expiration' => new \stdClass()
            ],
            'refresh_token_expiration_is_boolean' => [
                'refresh_token_expiration' => true
            ],
            'refresh_token_expiration_is_string' => [
                'refresh_token_expiration' => '1'
            ],
            'refresh_token_expiration_is_float' => [
                'refresh_token_expiration' => 1.1
            ],
            'refresh_token_expiration_is_negative' => [
                'refresh_token_expiration' => -1
            ],
            'refresh_token_expiration_is_zero' => [
                'refresh_token_expiration' => 0
            ],
        ];
    }

    /**
     * @test
     * @dataProvider validStringHashingAlgorithmProvider
     */
    public function it_can_set_and_get_a_valid_string_hashing_algorithm($value)
    {
        $config = new Config(
            ['hashing_algorithm' => $value]
        );

        $this->assertInstanceOf(HashingAlgorithm::class, $config->get('hashing_algorithm'));
        $this->assertEquals($value, $config->get('hashing_algorithm')->value);
    }

    public static function validStringHashingAlgorithmProvider(): array
    {
        return [
            'string_value_HS256' => [
                'value' => 'HS256'
            ],
            'string_value_HS384' => [
                'value' => 'HS384'
            ],
            'string_value_HS512' => [
                'value' => 'HS512'
            ],
        ];
    }

    /**
     * @test
     * @dataProvider validEnumHashingAlgorithmProvider
     */
    public function it_can_set_and_get_a_valid_enum_hashing_algorithm($value)
    {
        $config = new Config(
            ['hashing_algorithm' => $value]
        );

        $this->assertInstanceOf(HashingAlgorithm::class, $config->get('hashing_algorithm'));
        $this->assertEquals($value, $config->get('hashing_algorithm'));
    }

    public static function validEnumHashingAlgorithmProvider(): array
    {
        return [
            'enum_value_HS256' => [
                'value' => HashingAlgorithm::HS256
            ],
            'enum_value_HS384' => [
                'value' => HashingAlgorithm::HS384
            ],
            'enum_value_HS512' => [
                'value' => HashingAlgorithm::HS512
            ],
        ];
    }

    /**
     * @test
     * @dataProvider invalidHashingAlgorithmProvider
     */
    public function it_throws_an_exception_when_setting_an_invalid_hashing_algorithm($hashing_algorithm)
    {
        $this->expectException(\InvalidArgumentException::class);

        new Config(
            ['hashing_algorithm' => $hashing_algorithm]
        );
    }

    public static function invalidHashingAlgorithmProvider() // Anything that is not the HashingAlgorithm enum or an equivalent string
    {
        return [
            'hashing_algorithm_is_empty string' => [
                'hashing_algorithm' => ''
            ],
            'hashing_algorithm_is_array' => [
                'hashing_algorithm' => []
            ],
            'hashing_algorithm_is_object' => [
                'hashing_algorithm' => new \stdClass()
            ],
            'hashing_algorithm_is_boolean' => [
                'hashing_algorithm' => true
            ],
            'hashing_algorithm_is_string' => [
                'hashing_algorithm' => '1'
            ],
            'hashing_algorithm_is_float' => [
                'hashing_algorithm' => 1.1
            ],
            'hashing_algorithm_is_negative' => [
                'hashing_algorithm' => -1
            ],
            'hashing_algorithm_is_zero' => [
                'hashing_algorithm' => 0
            ],
            'hashing_algorithm_is_invalid' => [
                'hashing_algorithm' => 'invalidOrNonExistantAlgorithm'
            ],
        ];
    }

    /**
     * @test
     * @dataProvider validEnumPersistanceDriverProvider
     */
    public function it_can_set_and_get_a_valid_enum_persistance_driver($driver)
    {
        $config = new Config(
            ['persistance_driver' => $driver]
        );

        $this->assertEquals($driver, $config->get('persistance_driver'));
    }

    public static function validEnumPersistanceDriverProvider(): array
    {
        return [
            'enum_value_Redis' => [
                'persistance_driver' => Driver::Redis
            ],
            'enum_value_None' => [
                'persistance_driver' => Driver::None
            ],
        ];
    }

    /**
     * @test
     * @dataProvider invalidPersistanceDriverProvider
     */
    public function it_throws_an_exception_when_setting_an_invalid_persistance_driver($persistance_driver)
    {
        $this->expectException(\InvalidArgumentException::class);

        new Config(
            ['persistance_driver' => $persistance_driver]
        );
    }

    public static function invalidPersistanceDriverProvider() // Anything that is not the Driver enum
    {
        return [
            'persistance_driver_is_empty string' => [
                'persistance_driver' => ''
            ],
            'persistance_driver_is_array' => [
                'persistance_driver' => []
            ],
            'persistance_driver_is_object' => [
                'persistance_driver' => new \stdClass()
            ],
            'persistance_driver_is_boolean' => [
                'persistance_driver' => true
            ],
            'persistance_driver_is_string' => [
                'persistance_driver' => '1'
            ],
            'persistance_driver_is_float' => [
                'persistance_driver' => 1.1
            ],
            'persistance_driver_is_negative' => [
                'persistance_driver' => -1
            ],
            'persistance_driver_is_zero' => [
                'persistance_driver' => 0
            ],
            'persistance_driver_is_invalid' => [
                'persistance_driver' => 'invalidOrNonExistantDriver'
            ],
        ];
    }
    
}