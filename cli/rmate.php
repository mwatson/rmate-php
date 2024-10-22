<?php

// this is a really rough PHP port of Rmate:
// https://github.com/textmate/rmate/blob/master/bin/rmate

define('DATE', "2021-04-26");
define('VERSION', "1.5.10");
define('VERSION_STRING', "rmate-php version " . VERSION . " (" . DATE . ")");

include(__DIR__ . "/../src/Rmate/Rmate.php");
include(__DIR__ . "/../src/Rmate/CliOptions.php");
include(__DIR__ . "/../src/Rmate/Settings.php");
include(__DIR__ . "/../src/Rmate/Command.php");

if (realpathext($argv[0]) == realpath($_SERVER['PHP_SELF'] ?? '')) {
    array_shift($argv);
}

$settings = new \Rmate\Settings();
$cliOpts = new \Rmate\CliOptions($argv);

$argv = $cliOpts->parseCliOptions($settings);

// Parse arguments.
$cmds = [];

foreach ($argv as $idx => $path) {
    if ($path == '-') {
        echo "Reading from stdin, press ^D to stop\n";
    } else {
        if (is_dir($path)) {
            echo "'{$path}' is a directory! Aborting.\n";
            die;
        } else if (!$settings->force && !is_writable($path)) {
            echo "File {$path} is not writable! Use -f or --force to open anyway.\n";
            die;
        } else if (!is_writable($path) && $settings->verbose) {
            echo "File {$path} is not writable. Opening anyway.\n";
        }
    }

    $cmd = new \Rmate\Command("open");

    if ($path == '-') {
        $cmd->display_name = "{$settings->host}:untitled (stdin)";
    }
    if ($path != '-') {
        $cmd->display_name = "{$settings->host}:{$path}";
    }
    if (count($settings->names) > $idx) {
        $cmd->display_name = $settings->names[$idx];
    }
    if ($path != '-') {
        $cmd->real_path = realpathext($path);
    }

    $cmd->data_on_save = true;
    $cmd->re_activate = $settings->reactivate;
    $cmd->token = $path;

    if (count($settings->lines) > $idx) {
        $cmd->selection = $settings->lines[$idx];
    }
    if ($path == '-') {
        $cmd->file_type = 'txt';
    }
    if (count($settings->types) > $idx) {
        $cmd->file_type = $settings->types[$idx];
    }
    if ($path == '-') {
        // read from stdin
        $cmd->read_stdin();
    }
    if ($path != '-' && file_exists($path)) {
        $cmd->read_file($path);
    }
    if($path != '-' && !file_exists($path)) {
        $cmd->data = "0";
    }
    
    $cmds[] = $cmd;
}

$rmate = new \Rmate\Rmate($settings);

if ($settings->wait) {
    // run synchronously
    $rmate->connectAndHandleCmds($settings->host, $settings->port, $settings->unixsocket, $cmds);
} else {
    // run async, which is really annoying in PHP
    $cliArgs = $cliOpts->getCliArgs();
    // add wait arg so we don't fork bomb ourselves
    array_unshift($cliArgs, '-w');
    $cliArgs = implode(' ', $cliArgs);
    $rmate = __FILE__;

    $out = [];
    exec("php {$rmate} {$cliArgs} 2>&1 & echo $!", $out);
    $pid = (int) $out[0];
    echo "rmate PID: {$pid}\n";
}

// wrapper for if you need to debug calls to fwrite
function fsockwrite($fp, mixed $value) {
    //echo "SENDING {$value}\n";
    fwrite($fp, "{$value}\n");
}

// realpath that also expands ~
function realpathext(string $path) {
    return realpath(str_replace('~', getenv('HOME'), $path));
}
