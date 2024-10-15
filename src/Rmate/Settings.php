<?php

namespace Rmate;

class Settings {
    public $host;
    public $port;
    public $unixsocket;
    public $wait;
    public $force;
    public $verbose;
    public $lines;
    public $names;
    public $types;
    public $reactivate;

    public function __construct() {
        $this->host       = 'localhost';
        $this->port       = 52698;
        $this->unixsocket = '~/.rmate.socket';

        $this->wait       = false;
        $this->force      = false;
        $this->verbose    = true;
        $this->reactivate = true;
        $this->lines      = [];
        $this->names      = [];
        $this->types      = [];

        $this->read_disk_settings();

        if (getenv('RMATE_HOST')) {
            $this->host = strval(getenv('RMATE_HOST'));
        }
        if (getenv('RMATE_PORT')) {
            $this->port = intval(getenv('RMATE_PORT'));
        }
        if (getenv('RMATE_UNIXSOCKET')) {
            $this->unixsocket = strval(getenv('RMATE_UNIXSOCKET'));
        }

        // doesn't work yet
        $this->parse_cli_options();

        if ($this->host == 'auto') {
            $this->host = $this->parse_ssh_connection();
        }
    }

    protected function read_disk_settings() {
        foreach ([ "/etc/rmate.rc", "/usr/local/etc/rmate.rc", "~/.rmate.rc"] as $current_file) {
            $file = realpath($current_file);
            if (file_exists($file)) {
                // using JSON configs since it's built into PHP
                $params = json_decode(file_get_contents($file), true);
                $this->host       = $params['host'] ?? $this->host;
                $this->port       = $params['port'] ?? $this->port;
                $this->unixsocket = $params['unixsocket'] ?? $this->unixsocket;
            }
        }
    }

    // I don't think PHP has a CLI option parser
    protected function parse_cli_options() {

        $options = [
            [
                'option' => 'host',
                'short' => '',
                'type' => 'value',
                'description' => [
                    "Connect to host.",
                    "Use 'auto' to detect the host from SSH.",
                    "Defaults to {$this->host}.",
                ],
                'cb' => function(Settings $settings, string $val) {
                    $settings->host = $val;
                },
            ],
            [
                'option' => 'unixsocket',
                'short' => 's',
                'type' => 'value',
                'description' => [
                    "UNIX socket path.",
                    "Takes precedence over host/port if the file exists",
                    "Default {$this->unixsocket}",
                ],
                'cb' => function(Settings $settings, string $val) {
                    $settings->unixsocket = $val;
                },
            ],
            [
                'option' => 'port',
                'short' => 'p',
                'type' => 'value',
                'description' => [
                    "Port number to use for connection.",
                    "Defaults to {$this->port}.",
                ],
                'cb' => function(Settings $settings, int $val) {
                    $settings->port = $val;
                },
            ],
            [
                'option' => 'wait',
                'short' => 'w',
                'type' => 'flag',
                'description' => [
                    "Wait for file to be closed by TextMate.",
                ],
                'cb' => function(Settings $settings) {
                    $settings->wait = true;
                },
            ],
            [
                'option' => 'line',
                'short' => 'l',
                'type' => 'value',
                'description' => [
                    "Place caret on line [NUMBER] after loading file.",
                ],
                'cb' => function(Settings $settings, int $val) {
                    $settings->lines[] = $val;
                },
            ],
            [
                'option' => 'name',
                'short' => 'm',
                'type' => 'value',
                'description' => [
                    "The display name shown in TextMate.",
                ],
                'cb' => function(Settings $settings, int $val) {
                    $settings->names[] = $val;
                },
            ],
            [
                'option' => 'type',
                'short' => 't',
                'type' => 'value',
                'description' => [
                    "Treat file as having [TYPE].",
                ],
                'cb' => function(Settings $settings, string $val) {
                    $settings->types[] = $val;
                },
            ],
            [
                'option' => 'force',
                'short' => 'f',
                'type' => 'flag',
                'description' => [
                    "Open even if the file is not writable.",
                ],
                'cb' => function(Settings $settings) {
                    $settings->force = true;
                },
            ],
            [
                'option' => 'keep-focus',
                'short' => 'k',
                'type' => 'flag',
                'description' => [
                    "Have TextMate retain window focus after file is closed.",
                ],
                'cb' => function(Settings $settings) {
                    $settings->reactivate = true;
                },
            ],
            [
                'option' => 'verbose',
                'short' => 'v',
                'type' => 'flag',
                'description' => [
                    "Verbose logging messages.",
                ],
                'cb' => function(Settings $settings) {
                    $settings->verbose = true;
                },
            ],
            [
                'option' => 'help',
                'short' => 'h',
                'type' => 'info',
                'description' => [
                    "Show this message.",
                ],
                'cb' => function(Settings $settings) {
                    foreach ($options as $option) {
                        if ($option['short']) {
                            echo "-{$option['short']}, ";
                        } else {
                            echo "    ";
                        }

                        echo "--{$option['option']} ";
                        echo implode(' ', $option['description']);
                        echo "\n";
                    }
                    exit;
                },
            ],
            [
                'option' => 'version',
                'short' => '',
                'type' => 'info',
                'description' => [
                    "Show version.",
                ],
                'cb' => function(Settings $settings) {
                    echo VERSION_STRING . "\n";
                    exit;
                },
            ],
        ];
    }

    public function parse_ssh_connection() {
      return getenv('SSH_CONNECTION') ? reset(explode(' ', getenv('SSH_CONNECTION'))) : 'localhost';
    }
}
