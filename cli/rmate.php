<?php

// This is the main entrypoint for the script

// rmate-php is a PHP port of rmate:
// https://github.com/textmate/rmate/blob/master/bin/rmate

include(__DIR__ . "/bootstrap.php");

// get rid of the script name if we're being called via `php rmate.php`
if (\Rmate\RealPath::get($argv[0]) == \Rmate\RealPath::get($_SERVER['PHP_SELF'] ?? '')) {
    array_shift($argv);
}

$rmate = new \Rmate\Rmate(
    new \Rmate\Settings([ "/etc/rmate.rc", "/usr/local/etc/rmate.rc", "~/.rmate.rc" ]),
    new \Rmate\CliOptions($argv),
    new \Rmate\Output(),
    __FILE__
);

exit(0);
