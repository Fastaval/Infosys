<?php

namespace Fv\Tests\Entities;
use PHPUnit\Framework\TestCase;
use Deltagere;
use DateTimeImmutable;

class DeltagereTest extends TestCase
{
    public function testAgeFulfilment()
    {
        $ef = $this->getMockBuilder('EntityFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $participant = new Deltagere($ef);

        $this->assertTrue(is_a($participant, 'AgeFulfilment'));

        $this->assertEquals(-1, $participant->getAge());

        $then = new DateTimeImmutable('1978-04-18');
        $now = new DateTimeImmutable();
        $diff = $then->diff($now);

        $participant->birthdate = '1978-04-18';

        $this->assertEquals($diff->y, $participant->getAge());
    }
}
