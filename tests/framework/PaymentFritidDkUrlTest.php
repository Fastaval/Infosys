<?php

namespace Fv\Tests\Framework;
use PHPUnit\Framework\TestCase;
use PaymentFritidDkUrl;
use PaymentFritidDkApi;

class PaymentFritidDkUrlTest extends TestCase
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

        $payment = new PaymentFritidDkUrl(new PaymentFritidDkApi('test-key', $http));

        $url = PaymentFritidDkApi::APIURL;

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
            ->with('POST', $url, ['json' => $data, 'verify' => false])
            ->willReturn($response);

        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $response->expects($this->once())
            ->method('getBody')
            ->willReturn('{"message": "success", "url":"http://dev.fritid.dk/service/QJwedWk7tq3b"}');

        $this->assertEquals('http://dev.fritid.dk/service/QJwedWk7tq3b', $payment->generateOutput($participant, 100, $links));
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

        $payment = new PaymentFritidDkUrl(new PaymentFritidDkApi('test-key', $http));

        $url = PaymentFritidDkApi::APIURL;

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
            ->with('POST', $url, ['json' => $data, 'verify' => false])
            ->willReturn($response);

        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(400);

        $this->expectException('FrameworkException', 'Could not create ticket at fritid.dk');

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

        $payment = new PaymentFritidDkUrl(new PaymentFritidDkApi('test-key', $http));

        $url = PaymentFritidDkApi::APIURL;

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
            ->with('POST', $url, ['json' => $data, 'verify' => false])
            ->willReturn($response);

        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $response->expects($this->exactly(2))
            ->method('getBody')
            ->willReturn('');

        $this->expectException('FrameworkException', 'Data from fritid.dk makes no sense: ');

        $payment->generateOutput($participant, 100, $links);
    }
}
