<?php

define('DATE', "2021-04-26");
define('VERSION', "1.5.10");
define('VERSION_STRING', "rmate-php version " . VERSION . " (" . DATE . ")");

define('DEFAULT_HOST', "localhost");
define('DEFAULT_PORT', 52698);
define('DEFAULT_SOCKET', "~/.rmate.socket");

$composerAutoload = realpath(__DIR__ . "/../vendor/autoload.php");

if (file_exists($composerAutoload)) {
    include($composerAutoload);
} else {
    spl_autoload_register(function($className) {
        $basePath = realpath(__DIR__ . "/../src");
        $classPath = str_replace('\\', '/', $className);
        if (file_exists("{$basePath}/{$classPath}.php")) {
            include("{$basePath}/{$classPath}.php");
        }
    });
}
