<?php

namespace Fv\Tests;

require_once __DIR__ . '/../bootstrap.php';

class KarmaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * return rule set
     *
     * @access public
     * @return array
     */
    public function getRuleSet()
    {
        return [
                new \KarmaFlatValueRule('potential', 0, -7),
                new \KarmaDiscreteValuesRule('potential', 1, [1 => -12, 2 => -20, 3 => -26, 4 => -29, 5 => -32, 6 => -34, 7 => -35, 8 => -36]),
                new \KarmaDiscreteValuesRule('potential', 2, [1 => -1, 2 => -2, 3 => -3]),
                new \KarmaDiscreteValuesRule('potential', 3, [1 => -1]),
                new \KarmaDiscreteValuesRule('factual', 1, [1 => 10, 2 => 20, 3 => 30, 4 => 40, 5 => 50, 6 => 60, 7 => 70, 8 => 80, 9 => 90, 10 => 100]),
                new \KarmaDiscreteValuesRule('factual', 2, [1 => 7, 2 => 14, 3 => 21, 4 => 28, 5 => 35, 6 => 42, 7 => 49, 8 => 56, 9 => 63, 10 => 70]),
               ];
    }

    protected function getKarma()
    {
        $this->db = $this->getMockBuilder('DB')
            ->disableOriginalConstructor()
            ->setMethods(['query'])
            ->getMock();

        return new \Karma($this->db, $this->getRuleSet());
    }

    public function testYoungUnCalculation()
    {
        $karma = $this->getKarma();

        $participant = $this->getMockBuilder('Deltagere')
            ->disableOriginalConstructor()
            ->getMock();

        $return = [
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                  ];

        $this->db->method('query')
            ->willReturn($return);

        $this->assertEquals(14, $karma->calculate($participant));
    }

    public function testSuperGM()
    {
        $karma = $this->getKarma();

        $participant = $this->getMockBuilder('Deltagere')
            ->disableOriginalConstructor()
            ->getMock();


        $return = [
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 0,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 0,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 0,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 0,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 0,
                    'type'        => 'potential',
                   ],
                  ];

        $this->db->method('query')
            ->willReturn($return);

        $this->assertEquals(-3, $karma->calculate($participant));
    }

    public function testBoardGamer()
    {
        $karma = $this->getKarma();

        $participant = $this->getMockBuilder('Deltagere')
            ->disableOriginalConstructor()
            ->getMock();

        $return = [
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'factual',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 1,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 2,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                   [
                    'deltager_id' => 1,
                    'karmatype'   => 3,
                    'type'        => 'potential',
                   ],
                  ];

        $this->db->method('query')
            ->willReturn($return);

        $this->assertEquals(16, $karma->calculate($participant));
    }
}
