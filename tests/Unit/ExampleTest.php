<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psy\Util\Str;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function is_string_returned_of_name_method()
    {
        $str = \Illuminate\Support\Str::random(12);
        $this->assertIsString($str);
    }
}
