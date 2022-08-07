<?php

namespace Fv\Tests;

require_once __DIR__ . '/../bootstrap.php';

class FulfilmentBlobTest extends \PHPUnit_Framework_TestCase
{
    public function testAddFulfilment()
    {
        $blob = new \FulfilmentBlob();

        $this->assertEquals(0, count($blob->getFulfilments()));

        $participant = $this->getMockBuilder('Deltagere')
            ->disableOriginalConstructor()
            ->getMock();

        $blob->addFulfilment($participant);

        $this->assertEquals(1, count($blob->getFulfilments()));
    }
}
