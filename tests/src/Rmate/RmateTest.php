<?php

namespace Rmate\Tests;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

use Rmate;

class RmateTest extends TestCase
{
    public function testConstructorBuildsAndRunsCommandHandler()
    {
        $connection = $this
            ->getMockBuilder(Rmate\Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'connect',
                'nextLine',
                'write',
                'isEof',
                'close',
            ])
            ->getMock();

        $connection
            ->expects($this->once())
            ->method('connect');
        $connection
            ->expects($this->once())
            ->method('nextLine');
        $connection
            ->expects($this->exactly(10))
            ->method('write');
        $connection
            ->expects($this->once())
            ->method('isEof')
            ->willReturn(true);
        $connection
            ->expects($this->once())
            ->method('close');

        $root = vfsStream::setup('root', null, [
            'some-file.txt' => "just a plain text file\n",
        ]);

        $cliOpts = new Rmate\CliOptions([ '-w', $root->getChild("some-file.txt")->url() ]);

        $settings = new Rmate\Settings();

        $cliOpts->parseCliOptions($settings, new Rmate\Output());

        $rmate = new Rmate\Rmate(
            $settings,
            new Rmate\Output(),
            $connection,
            $cliOpts,
            'dont_fork_me'
        );
    }
}
