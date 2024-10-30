<?php

namespace Rmate\Tests;

use PHPUnit\Framework\TestCase;

use Rmate;

class CliOptionsTest extends TestCase
{
    public function testParseCliOption()
    {
        $cliOpts = new Rmate\CliOptions([ '--host=testhost', '-' ]);
        $settings = new Rmate\Settings();

        $remainingArgs = $cliOpts->parseCliOptions($settings);

        $this->assertEquals([ '-' ], $remainingArgs);
        $this->assertEquals('testhost', $settings->host);
    }

    public function testParseShortCliOption()
    {
        $cliOpts = new Rmate\CliOptions([ '-w', '-' ]);
        $settings = new Rmate\Settings();

        $remainingArgs = $cliOpts->parseCliOptions($settings);

        $this->assertEquals([ '-' ], $remainingArgs);
        $this->assertTrue($settings->wait);
    }
}