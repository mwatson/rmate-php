<?php

namespace Rmate\Tests;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

use Rmate;

class SettingsTest extends TestCase
{
    protected $root;

    public function setUp() : void
    {
        $this->root = vfsStream::setup('root', null, [
            'etc' => [
                'rmate.rc' => '{"port":12,"host":"somehost.biz"}',
            ],
        ]);

        $realPath = $this
            ->getMockBuilder(Rmate\Realpath::class)
            ->onlyMethods([ 'realPath' ])
            ->getMock();

        $realPath
            ->expects($this->any())
            ->method('realPath')
            ->willReturnArgument(0);

        Rmate\Realpath::$instance = $realPath;
    }

    public function testConstructorBuildsExpectedSettings()
    {
        $settings = new Rmate\Settings([
            $this->root->getChild('etc/rmate.rc')->url(),
        ]);

        $this->assertEquals("somehost.biz", $settings->host);
        $this->assertEquals(12, $settings->port);
    }
}
