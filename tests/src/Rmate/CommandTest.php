<?php

namespace Rmate\Tests;

use PHPUnit\Framework\TestCase;

use Rmate;

class CommandTest extends TestCase
{
    public function testSetVariablesAndSend()
    {
        $connection = $this
            ->getMockBuilder(Rmate\Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods([ 'write' ])
            ->getMock();

        $connection
            ->expects($this->exactly(4))
            ->method('write')
            ->willReturnCallback(function($input) {
                echo "{$input}\n";
                return 1;
            });

        $command = new Rmate\Command("test");
        $command->var_name = true;
        $command->another_var = "string val";

        $this->expectOutputString(
            "test\nvar-name:yes\nanother-var:string val\n\n"
        );

        $command->send($connection);
    }
}
