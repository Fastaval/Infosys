<?php

namespace Fv\Tests;

require_once __DIR__ . '/../bootstrap.php';

class AgeFulfilmentTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAge()
    {
        $participant = $this->getMockBuilder('Deltagere')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->assertEquals(-1, $participant->getAge());

        $participant->birthdate = date('Y-m-d', strtotime('now - 2 years'));

        $this->assertEquals(2, $participant->getAge());
    }

    public function testIsYoungerThan()
    {
        $participant = $this->getMockBuilder('Deltagere')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->assertTrue($participant->isYoungerThan(12));

        $participant->birthdate = date('Y-m-d', strtotime('now - 2 years'));

        $this->assertTrue($participant->isYoungerThan(12));

        $participant->birthdate = date('Y-m-d', strtotime('now - 12 years'));

        $this->assertFalse($participant->isYoungerThan(12));

        $participant->birthdate = date('Y-m-d', strtotime('now - 11 years 360 days'));

        $this->assertTrue($participant->isYoungerThan(12));
    }

    public function testIsOlderThan()
    {
        $participant = $this->getMockBuilder('Deltagere')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->assertFalse($participant->isOlderThan(12));

        $participant->birthdate = date('Y-m-d', strtotime('now - 2 years'));

        $this->assertFalse($participant->isOlderThan(12));

        $participant->birthdate = date('Y-m-d', strtotime('now - 12 years'));

        $this->assertTrue($participant->isOlderThan(12));

        $participant->birthdate = date('Y-m-d', strtotime('now - 11 years 360 days'));

        $this->assertFalse($participant->isOlderThan(12));
    }
}
