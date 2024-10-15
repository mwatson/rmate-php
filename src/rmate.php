<?php

define('VERBOSE', true); // -w

// this is a really rough PHP port of Rmate:
// https://github.com/textmate/rmate/blob/master/bin/rmate

define('DATE', "2021-04-26");
define('VERSION', "1.5.10");
define('VERSION_STRING', "rmate version " . VERSION . " (" . DATE . ")");

// MAIN

include ( __DIR__ . "/Rmate/Rmate.php");
include ( __DIR__ . "/Rmate/Settings.php");
include ( __DIR__ . "/Rmate/Command.php");


$settings = new \Rmate\Settings();

$rmate = new \Rmate\Rmate($settings);

// Parse arguments.
$cmds = [];

if (realpath($argv[0]) == __FILE__) {
    array_shift($argv);
}

foreach ($argv as $idx => $path) {
    if ($path == '-') {
        echo "Reading from stdin, press ^D to stop\n";
        // read from stdin here
        // ... if we want to
    } else {
        if (is_dir($path)) {
            echo "'{$path}' is a directory! Aborting.\n";
            die;
        } else if (!$settings->force && !is_writable($path)) {
            echo "File {$path} is not writable! Use -f/--force to open anyway.\n";
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
        $cmd->real_path    = realpath($path);
    }
    $cmd->data_on_save = true;
    $cmd->re_activate  = $settings->reactivate;
    $cmd->token        = $path;
    if (count($settings->lines) > $idx) {
        $cmd->selection    = $settings->lines[$idx];
    }
    if ($path == '-') {
        $cmd->file_type    = 'txt';
    }
    if (count($settings->types) > $idx) {
        $cmd->file_type    = $settings->types[$idx];
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

if ($settings->wait) {
    $rmate->connect_and_handle_cmds($settings->host, $settings->port, $settings->unixsocket, $cmds);
} else {
    // TODO fork/spawn here
    $rmate->connect_and_handle_cmds($settings->host, $settings->port, $settings->unixsocket, $cmds);
}

// goofy helper/wrapper for if you need to debug
function fsockwrite($fp, $value) {
    fwrite($fp, "{$value}\n");
}
