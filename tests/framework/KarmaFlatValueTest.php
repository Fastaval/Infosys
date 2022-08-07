<?php

namespace Fv\Tests;

require_once __DIR__ . '/../bootstrap.php';

class KarmaFlatValueTest extends \PHPUnit_Framework_TestCase
{
    public function testRule()
    {
        $rule = new \KarmaFlatValueRule('potential', 0, -10);

        $this->assertEquals(-10, $rule->calculate(
            [
             'potential' => [
                             1,
                             1,
                             1,
                             0,
                             0,
                            ],
            ]
        ));
    }

    public function testEmptyData()
    {
        $rule = new \KarmaFlatValueRule('potential', 0, -10);

        $this->assertEquals(0, $rule->calculate([]));
    }

    public function testNoMatch()
    {
        $rule = new \KarmaFlatValueRule('potential', 0, -10);

        $this->assertEquals(0, $rule->calculate(
            [
             'potential' => [
                             1,
                             1,
                             1,
                            ],
            ]
        ));
    }
}
