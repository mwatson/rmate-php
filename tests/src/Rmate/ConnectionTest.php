<?php

namespace Rmate\Tests;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

use Rmate;

class ConnectionTest extends TestCase
{
    protected $root;

    public function setUp() : void
    {
        $this->root = vfsStream::setup('root', null, [
            'unix.sock' => 'socket_data_here?',
        ]);
    }

    public function testConnectionCreateTcpSuccess()
    {
        $connection = new Rmate\Connection();

        $this->assertEquals("TCP", $connection->getType());
    }

    public function testConnectionCreateUnixSuccess()
    {
        $realPath = $this
            ->getMockBuilder(Rmate\Realpath::class)
            ->onlyMethods([ 'realPath' ])
            ->getMock();

        $realPath
            ->expects($this->any())
            ->method('realPath')
            ->willreturnArgument(0);

        Rmate\Realpath::$instance = $realPath;

        $connection = new Rmate\Connection(
            DEFAULT_HOST,
            DEFAULT_PORT,
            $this->root->getChild('unix.sock')->url()
        );

        $this->assertEquals("UNIX", $connection->getType());
    }
}
