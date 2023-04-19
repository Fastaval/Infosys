<?php

namespace Fv\Tests\Framework;
use PHPUnit\Framework\TestCase;

class PaymentFactoryTest extends TestCase
{
    public function testCreate_fritidDkLink()
    {
        $config = new \ArrayConfig([
            'payment.type'   => 'FritidDkLink',
            'payment.apikey' => 'fWo23F5JPpDjowpjOppq24lsfF213gbg1Zm',
        ]);

        $factory = new \PaymentFactory($config);

        $module = $factory->build();

        $this->assertTrue($module instanceof \PaymentFritidDkLink);
        $this->assertTrue($module instanceof \PaymentLink);
        $this->assertTrue($module instanceof \PaymentConnector);
    }

    public function testCreate_fritidDkUrl()
    {
        $config = new \ArrayConfig([
            'payment.type'   => 'FritidDkUrl',
            'payment.apikey' => 'fWo23F5JPpDjowpjOppq24lsfF213gbg1Zm',
        ]);

        $factory = new \PaymentFactory($config);

        $module = $factory->build();

        $this->assertTrue($module instanceof \PaymentFritidDkUrl);
        $this->assertTrue($module instanceof \PaymentUrl);
        $this->assertTrue($module instanceof \PaymentConnector);
    }
}
