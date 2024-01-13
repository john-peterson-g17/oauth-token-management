<?php

namespace JohnPetersonG17\OAuthTokenManagement\Tests\Unit;

use JohnPetersonG17\OAuthTokenManagement\Token\TokenPurpose;
use JohnPetersonG17\OAuthTokenManagement\Token\Token;
use JohnPetersonG17\OAuthTokenManagement\Token\TokenFactory;
use DateInterval;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class TokenFactoryTest extends TestCase {

    private const VALID_RAW_TOKEN = [
        'jti' => 'someTokenId',
        'iss' => 'someIssuer',
        'sub' => 1,
        'prp' => TokenPurpose::ACCESS->value,
        'iat' => 1703392480,
        'exp' => 1703392480,
    ];

    private TokenFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new TokenFactory(
            'someIssuer',
            60,
            600,
        );
    }

    /**
     * @test
     */
    public function it_can_make_a_token_correctly()
    {
        $expectedUserId = 1;
        $expectedIssuer = 'someIssuer';
        $this->factory->setIssuer($expectedIssuer);
        $this->factory->setAccessTokenExpiration(60);

        $token = $this->factory->make($expectedUserId, TokenPurpose::ACCESS);

        $interval = DateInterval::createFromDateString(60 . ' seconds'); // Same access token expiration we set above
        $expectedExpiration = $this->factory->getMostRecentIssuedAt()->add($interval); // IssuedAt + 60 seconds

        $expectedTimeZone = new DateTimeZone('utc'); // Expect UTC timezone

        $this->assertInstanceOf(Token::class, $token);
        $this->assertIsString($token->id()); // UUID String
        $this->assertEquals($expectedIssuer, $token->issuer());
        $this->assertEquals($expectedUserId, $token->subject());
        $this->assertEquals(TokenPurpose::ACCESS, $token->purpose());
        $this->assertEquals($expectedTimeZone->getName(), $token->issuedAt()->getTimezone()->getName());
        $this->assertEquals($this->factory->getMostRecentIssuedAt()->getTimeStamp(), $token->issuedAt()->getTimestamp());
        $this->assertEquals($expectedTimeZone->getName(), $token->expiresAt()->getTimezone()->getName());
        $this->assertEquals($expectedExpiration->getTimeStamp(), $token->expiresAt()->getTimestamp());
    }

    /**
     * @test
     */
    public function it_can_make_a_refresh_token_with_the_correct_expiration_time()
    {
        $this->factory->setRefreshTokenExpiration(600);

        $token = $this->factory->make(1, TokenPurpose::REFRESH);

        $interval = DateInterval::createFromDateString(600 . ' seconds'); // Same refresh token expiration we set above
        $expectedExpiration = $this->factory->getMostRecentIssuedAt()->add($interval); // IssuedAt + 600 seconds

        $this->assertEquals($expectedExpiration->getTimeStamp(), $token->expiresAt()->getTimestamp());
    }

    /**
     * @test
     */
    public function it_makes_a_token_correctly_from_a_valid_array()
    {
        $token = $this->factory->makeFromRaw(self::VALID_RAW_TOKEN);

        $expectedTimeZone = new DateTimeZone('utc'); // Expect UTC timezone

        $this->assertEquals(self::VALID_RAW_TOKEN['jti'], $token->id());
        $this->assertEquals(self::VALID_RAW_TOKEN['iss'], $token->issuer());
        $this->assertEquals(self::VALID_RAW_TOKEN['sub'], $token->subject());
        $this->assertInstanceOf(TokenPurpose::class, $token->purpose());
        $this->assertEquals(self::VALID_RAW_TOKEN['prp'], $token->purpose()->value);
        $this->assertEquals($expectedTimeZone->getName(), $token->issuedAt()->getTimezone()->getName());
        $this->assertEquals(self::VALID_RAW_TOKEN['iat'], $token->issuedAt()->getTimestamp());
        $this->assertEquals($expectedTimeZone->getName(), $token->expiresAt()->getTimezone()->getName());
        $this->assertEquals(self::VALID_RAW_TOKEN['exp'], $token->expiresAt()->getTimestamp());
    }

    /**
     * @test
     * @dataProvider invalidRawTokenProvider
     */
    public function it_throws_an_exception_when_making_a_token_from_an_invalid_raw_token_array($name, $value)
    {
        // Take the valid token and modify it to be invalid based on the data provider
        $rawToken = self::VALID_RAW_TOKEN;
        if ($value == 'missing'){
            unset($rawToken[$name]);
        } else {
            $rawToken[$name] = $value;
        }

        $this->expectException(\InvalidArgumentException::class);

        $this->factory->makeFromRaw($rawToken);
    }

    public static function invalidRawTokenProvider(): array
    {
        return [
            // jti validation
            'missing_jti' => [
                'name' => 'jti',
                'value' => 'missing'
            ],
            'empty_jti' => [
                'name' => 'jti',
                'value' => '',
            ],
            // iss validation
            'missing_iss' => [
                'name' => 'iss',
                'value' => 'missing',
            ],
            'empty_iss' => [
                'name' => 'iss',
                'value' => '',
            ],
            'non_string_iss' => [
                'name' => 'iss',
                'value' => 1234,
            ],
            // sub validation
            'missing_sub' => [
                'name' => 'sub',
                'value' => 'missing',
            ],
            'empty_sub' => [
                'name' => 'sub',
                'value' => '',
            ],
            // prp validation
            'missing_prp' => [
                'name' => 'prp',
                'value' => 'missing',
            ],
            'empty_prp' => [
                'name' => 'prp',
                'value' => '',
            ],
            'non_string_prp' => [
                'name' => 'prp',
                'value' => 1234,
            ],
            'non_enum_value_prp' => [
                'name' => 'prp',
                'value' => 'someInvalidValue',
            ],
            // iat validation
            'missing_iat' => [
                'name' => 'iat',
                'value' => 'missing',
            ],
            'non_integer_iat' => [
                'name' => 'iat',
                'value' => 'someNonIntegerValue',
            ],
            // exp validation
            'missing_exp' => [
                'name' => 'exp',
                'value' => 'missing',
            ],
            'non_integer_exp' => [
                'name' => 'exp',
                'value' => 'someNonIntegerValue',
            ],
        ];
    }
}