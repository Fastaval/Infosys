<?php

namespace Fv\Tests;

require_once __DIR__ . '/../bootstrap.php';

class KarmaDiscreteValuesTest extends \PHPUnit_Framework_TestCase
{
    public function getRule()
    {
        $values = [
                   1 => -12,
                   2 => -22,
                   3 => -30,
                   4 => -36,
                   5 => -40,
                   6 => -42,
                   7 => -44,
                  ];

        return new \KarmaDiscreteValuesRule('potential', 1, $values);
    }

    public function testRule()
    {
        $rule = $this->getRule();

        $this->assertEquals(-30, $rule->calculate(
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
        $rule = $this->getRule();

        $this->assertEquals(0, $rule->calculate([]));
    }

    public function testNoMatch()
    {
        $rule = $this->getRule();

        $this->assertEquals(0, $rule->calculate(
            [
             'potential' => [
                             0,
                             0,
                             0,
                            ],
            ]
        ));
    }

    public function testRuleMaxed()
    {
        $rule = $this->getRule();

        $this->assertEquals(-44, $rule->calculate(
            [
             'potential' => [
                             1,
                             1,
                             1,
                             1,
                             1,
                             1,
                             1,
                             1,
                             1,
                             0,
                             0,
                            ],
            ]
        ));
    }
}
