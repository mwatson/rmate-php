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

        if ($this->host == 'auto') {
            $this->host = $this->parse_ssh_connection();
        }
    }

    protected function read_disk_settings() {
        foreach ([ "/etc/rmate.rc", "/usr/local/etc/rmate.rc", "~/.rmate.rc"] as $current_file) {
            $file = realpathext($current_file);
            if (file_exists($file)) {
                // using JSON configs since it's built into PHP
                $params = json_decode(file_get_contents($file), true);
                $this->host       = $params['host'] ?? $this->host;
                $this->port       = $params['port'] ?? $this->port;
                $this->unixsocket = $params['unixsocket'] ?? $this->unixsocket;
            }
        }
    }

    public function parse_ssh_connection() {
      return getenv('SSH_CONNECTION') ? reset(explode(' ', getenv('SSH_CONNECTION'))) : 'localhost';
    }
}
