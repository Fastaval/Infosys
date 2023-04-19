<?php

namespace Fv\Tests\Framework;
use PHPUnit\Framework\TestCase;
use DataUri;

class DataUriTest extends TestCase
{
    public function testGetType_png()
    {
        $data_uri = new DataUri('data:image/png;base64,blah');

        $this->assertEquals('image/png', $data_uri->getType());
    }

    public function testGetType_jpeg()
    {
        $data_uri = new DataUri('data:image/jpeg;base64,blah');

        $this->assertEquals('image/jpeg', $data_uri->getType());
    }

    public function testGetType_gif()
    {
        $data_uri = new DataUri('data:IMAGE/GIF;base64,blah');

        $this->assertEquals('image/gif', $data_uri->getType());
    }

    public function testConstruction_empty()
    {
        $this->expectException('FrameworkException', 'Construction value for DataUri must be a proper DataUri');
        $data_uri = new DataUri('');
    }

    public function testGetType_default()
    {
        $data_uri = new DataUri('data,');

        $this->assertEquals('text/plain', $data_uri->getType());
    }

    public function testConstruction_onlyBase64()
    {
        $data_uri = new DataUri('data;base64,');

        $this->assertEquals('text/plain', $data_uri->getType());
    }

    public function testIsEncoded_notEncoded()
    {
        $data_uri = new DataUri('data:image/png,');

        $this->assertEquals(false, $data_uri->isEncoded());
    }

    public function testIsEncoded_encoded()
    {
        $data_uri = new DataUri('data;base64,');

        $this->assertEquals(true, $data_uri->isEncoded());
    }

    public function testGetRawContent()
    {
        $data_uri = new Datauri('data,hahaha');

        $this->assertEquals('hahaha', $data_uri->getRawContent());
    }

    public function testGetContent_encoded()
    {
        $data_uri = new Datauri('data;base64,' . base64_encode('hahaha'));

        $this->assertEquals('hahaha', $data_uri->getContent());
    }

    public function testGetContent_notEncoded()
    {
        $data_uri = new Datauri('data,hahaha');

        $this->assertEquals('hahaha', $data_uri->getContent());
    }
}
