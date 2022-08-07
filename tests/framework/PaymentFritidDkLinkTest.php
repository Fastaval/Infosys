<?php

namespace Fv\Tests;

require_once __DIR__ . '/../bootstrap.php';

class PaymentFritidDkLinkTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateOutput_checkGood()
    {
        $http = $this->getMockBuilder('GuzzleHttp\\Client')
            ->setMethods(['request'])
            ->getMock();

        $participant = $this->getMockBuilder('Deltagere')
            ->disableOriginalConstructor()
            ->setMethods(['__get'])
            ->getMock();

        $participant->method('__get')
            ->with('email')
            ->willReturn('test@example.com');

        $payment = new \PaymentFritidDkLink(new \PaymentFritidDkApi('test-key', $http));

        $url = \PaymentFritidDkApi::APIURL;

        $data = [
            'fritid_key'   => 'test-key',
            'price'        => 100,
            'success_url'  => 'success',
            'callback_url' => "callback",
            'cancel_url'   => 'cancel',
            'email'        => 'test@example.com',
        ];

        $links = [
            'success_url'  => 'success',
            'callback_url' => "callback",
            'cancel_url'   => 'cancel',
        ];

        $response = $this->getMockBuilder('GuzzleHttp\\Psr7\\Response')
            ->setMethods(['getStatusCode', 'getBody'])
            ->getMock();

        $http->expects($this->once())
            ->method('request')
            ->with('POST', $url, ['json' => $data])
            ->willReturn($response);

        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $response->expects($this->once())
            ->method('getBody')
            ->willReturn('{"data":{"message": "success", "url":"http://dev.fritid.dk/service/QJwedWk7tq3b"}}');

        $this->assertEquals('<a href="http://dev.fritid.dk/service/QJwedWk7tq3b" class="paymentFritidDk">Betal nu</a>', $payment->generateOutput($participant, 100, $links));
    }

    public function testGenerateOutput_wrongStatus()
    {
        $http = $this->getMockBuilder('GuzzleHttp\\Client')
            ->setMethods(['request'])
            ->getMock();

        $participant = $this->getMockBuilder('Deltagere')
            ->disableOriginalConstructor()
            ->setMethods(['__get'])
            ->getMock();

        $participant->method('__get')
            ->with('email')
            ->willReturn('test@example.com');

        $payment = new \PaymentFritidDkLink(new \PaymentFritidDkApi('test-key', $http));

        $url = \PaymentFritidDkApi::APIURL;

        $data = [
            'fritid_key'   => 'test-key',
            'price'        => 100,
            'success_url'  => 'success',
            'callback_url' => "callback",
            'cancel_url'   => 'cancel',
            'email'        => 'test@example.com',
        ];

        $links = [
            'success_url'  => 'success',
            'callback_url' => "callback",
            'cancel_url'   => 'cancel',
        ];

        $response = $this->getMockBuilder('GuzzleHttp\\Psr7\\Response')
            ->setMethods(['getStatusCode', 'getBody'])
            ->getMock();

        $http->expects($this->once())
            ->method('request')
            ->with('POST', $url, ['json' => $data])
            ->willReturn($response);

        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(400);

        $this->setExpectedException('FrameworkException', 'Could not create ticket at fritid.dk');

        $payment->generateOutput($participant, 100, $links);
    }

    public function testGenerateOutput_badResponse()
    {
        $http = $this->getMockBuilder('GuzzleHttp\\Client')
            ->setMethods(['request'])
            ->getMock();

        $participant = $this->getMockBuilder('Deltagere')
            ->disableOriginalConstructor()
            ->setMethods(['__get'])
            ->getMock();

        $participant->method('__get')
            ->with('email')
            ->willReturn('test@example.com');

        $payment = new \PaymentFritidDkLink(new \PaymentFritidDkApi('test-key', $http));

        $url = \PaymentFritidDkApi::APIURL;

        $data = [
            'fritid_key'   => 'test-key',
            'price'        => 100,
            'success_url'  => 'success',
            'callback_url' => "callback",
            'cancel_url'   => 'cancel',
            'email'        => 'test@example.com',
        ];

        $links = [
            'success_url'  => 'success',
            'callback_url' => "callback",
            'cancel_url'   => 'cancel',
        ];

        $response = $this->getMockBuilder('GuzzleHttp\\Psr7\\Response')
            ->setMethods(['getStatusCode', 'getBody'])
            ->getMock();

        $http->expects($this->once())
            ->method('request')
            ->with('POST', $url, ['json' => $data])
            ->willReturn($response);

        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $response->expects($this->exactly(2))
            ->method('getBody')
            ->willReturn('');

        $this->setExpectedException('FrameworkException', 'Data from fritid.dk makes no sense: ');

        $payment->generateOutput($participant, 100, $links);
    }

    public function testGenerateOutput_lackingLinks()
    {
        $http = $this->getMockBuilder('GuzzleHttp\\Client')
            ->setMethods(['request'])
            ->getMock();

        $participant = $this->getMockBuilder('Deltagere')
            ->disableOriginalConstructor()
            ->getMock();

        $payment = new \PaymentFritidDkLink(new \PaymentFritidDkApi('test-key', $http));

        $url = \PaymentFritidDkApi::APIURL;

        $http->expects($this->never())
            ->method('request');

        $this->setExpectedException('FrameworkException', 'Setup data lacks connection links: success, cancel, callback');

        $payment->generateOutput($participant, 100, []);
    }
}
