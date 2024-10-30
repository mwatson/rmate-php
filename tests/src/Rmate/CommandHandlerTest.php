<?php

namespace Rmate\Tests;

use PHPUnit\Framework\TestCase;

use Rmate;

class CommandHandlerTest extends TestCase
{
    public function testConnectAndHandleCmdsSuccess()
    {
        $connection = $this
            ->getMockBuilder(Rmate\Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'connect',
                'nextLine',
                'read',
                'write',
                'isEof',
                'close',
            ])
            ->getMock();

        $connection
            ->expects($this->once())
            ->method('connect')
            ->willReturn(true);

        $connection
            ->expects($this->exactly(3))
            ->method('nextLine')
            ->willReturn(
                "Test Server Name",
                "close",
                "",
            );

        $connection
            ->expects($this->exactly(2))
            ->method('isEof')
            ->willReturn(
                false,
                true,
            );

        $connection
            ->expects($this->exactly(5))
            ->method('write')
            ->willReturn(1);

        $handler = new Rmate\CommandHandler($connection);

        $handler->connectAndHandleCmds([
            new Rmate\Command('test1'),
            new Rmate\Command('test2'),
        ]);
    }
}
