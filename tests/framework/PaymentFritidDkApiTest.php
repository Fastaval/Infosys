<?php

namespace Fv\Tests;

require_once __DIR__ . '/../bootstrap.php';

class PaymentFritidDkUrlTest extends \PHPUnit_Framework_TestCase
{
    public function testParseCallbackRequest_properData()
    {
        $http = $this->getMockBuilder('GuzzleHttp\\Client')
            ->getMock();

        $request = $this->getMockBuilder('Request')
            ->disableOriginalConstructor()
            ->setMethods(['__get'])
            ->getMock();

        $data = new \StdClass();
        $data->fees = 150;
        $data->cost = 950;

        $request->expects($this->once())
            ->method('__get')
            ->willReturn($data);

        $payment = new \PaymentFritidDkApi('test-key', $http);

        $parse_data = $payment->parseCallbackRequest($request);

        $expected = [
            'amount' => 1100,
            'fees'   => 150,
            'cost'   => 950,
        ];

        $this->assertEquals($expected, $parse_data);
    }

    public function testParseCallbackRequest_noData()
    {
        $http = $this->getMockBuilder('GuzzleHttp\\Client')
            ->getMock();

        $request = $this->getMockBuilder('Request')
            ->disableOriginalConstructor()
            ->setMethods(['__get'])
            ->getMock();

        $data = new \StdClass();

        $request->expects($this->once())
            ->method('__get')
            ->willReturn($data);

        $payment = new \PaymentFritidDkApi('test-key', $http);

        $parse_data = $payment->parseCallbackRequest($request);

        $this->assertEquals(false, $parse_data);
    }
}
