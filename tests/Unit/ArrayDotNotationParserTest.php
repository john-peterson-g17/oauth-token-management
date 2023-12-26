<?php

namespace JohnPetersonG17\JwtAuthentication\Tests\Unit;

use JohnPetersonG17\JwtAuthentication\Helpers\ArrayDotNotationParser;
use PHPUnit\Framework\TestCase;

class ArrayDotNotationParserTest extends TestCase {

    /**
     * @test
     * @dataProvider dotNotationDataProvider
     */
    public function it_can_parse_an_array_by_dot_notation($array, $key, $expected)
    {
        $this->assertEquals($expected, ArrayDotNotationParser::parse($array, $key));
    }

    public static function dotNotationDataProvider()
    {
        return [
            'existing_value_on_level_1_of_array' => [
                ['some' => ['nested' => ['array' => 'value']]],
                'some',
                ['nested' => ['array' => 'value']],
            ],
            'existing_value_on_level_2_of_array' => [
                ['some' => ['nested' => ['array' => 'value']]],
                'some.nested',
                ['array' => 'value'],
            ],
            'existing_value_on_level_3_of_array' => [
                ['some' => ['nested' => ['array' => 'value']]],
                'some.nested.array',
                'value',
            ],
            'non_existing_value_deep_in_array' => [
                ['some' => ['nested' => ['array' => 'value']]],
                'some.nested.array.key.that.does.not.exist',
                null,
            ],
        ];
    }
}