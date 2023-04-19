<?php

namespace Fv\Tests\Framework;
use PHPUnit\Framework\TestCase;
use FulfilmentBlob;

class FulfilmentBlobTest extends TestCase
{
    public function testAddFulfilment()
    {
        $blob = new FulfilmentBlob();

        $this->assertEquals(0, count($blob->getFulfilments()));

        $participant = $this->getMockBuilder('Deltagere')
            ->disableOriginalConstructor()
            ->getMock();

        $blob->addFulfilment($participant);

        $this->assertEquals(1, count($blob->getFulfilments()));
    }
}
