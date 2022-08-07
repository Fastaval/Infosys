<?php

namespace Fv\Tests;

require_once __DIR__ . '/../bootstrap.php';

class DeltagereTest extends \PHPUnit_Framework_TestCase
{
    public function testAgeFulfilment()
    {
        $ef = $this->getMockBuilder('EntityFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $participant = new \Deltagere($ef);

        $this->assertTrue(is_a($participant, 'AgeFulfilment'));

        $this->assertEquals(-1, $participant->getAge());

        //hackish check
        $years = (time() - strtotime('1978-04-18')) / (365 * 24 * 3600);

        $participant->birthdate = '1978-04-18';

        $this->assertEquals(floor($years), $participant->getAge());
    }
}
