<?php

namespace Rmate\Tests;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

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
            ->method('connect');

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

        $connection
            ->expects($this->once())
            ->method('close');

        $handler = new Rmate\CommandHandler($connection, new Rmate\Output());

        $handler->connectAndHandleCmds([
            new Rmate\Command('test1'),
            new Rmate\Command('test2'),
        ]);
    }

    public function testHandleSaveSuccessTest()
    {
        $root = vfsStream::setup('root', null, [
            'path' => [
                'file.php' => "<?php\necho 'PHP is cool';\n",
            ],
        ]);

        $connection = $this
            ->getMockBuilder(Rmate\Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'connect',
                'write',
                'isEof',
                'nextLine',
                'close',
            ])
            ->getMock();

        $connection
            ->expects($this->once())
            ->method('connect');

        $connection
            ->expects($this->exactly(1))
            ->method('write')
            ->willReturn(1);

        $connection
            ->expects($this->exactly(2))
            ->method('isEof')
            ->willReturn(
                false,
                true,
            );

        $connection
            ->expects($this->exactly(4))
            ->method('nextLine')
            ->willReturn(
                "Test Server Name",
                "save",
                "token: " . $root->getChild('path/file.php')->url(),
                "",
            );

        $connection
            ->expects($this->once())
            ->method('close');

        $handler = new Rmate\CommandHandler($connection, new Rmate\Output());

        $handler->connectAndHandleCmds([]);
    }
}
