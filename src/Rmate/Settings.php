<?php

namespace Rmate;

class Settings
{
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

    /**
     */
    public function __construct(array $configPaths = [])
    {
        $this->host       = DEFAULT_HOST;
        $this->port       = DEFAULT_PORT;
        $this->unixsocket = DEFAULT_SOCKET;

        $this->wait       = false;
        $this->force      = false;
        $this->verbose    = true;
        $this->reactivate = true;
        $this->lines      = [];
        $this->names      = [];
        $this->types      = [];

        $this->readDiskSettings($configPaths);

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
            $this->host = $this->parseSshConnection();
        }
    }

    /**
     * @return void
     */
    protected function readDiskSettings(array $configPaths) : void
    {
        foreach ($configPaths as $current_file) {
            $file = RealPath::get($current_file);
            if (file_exists($file)) {
                // using JSON configs since it's built into PHP
                $params = json_decode(file_get_contents($file), true);
                $this->host       = $params['host'] ?? $this->host;
                $this->port       = $params['port'] ?? $this->port;
                $this->unixsocket = $params['unixsocket'] ?? $this->unixsocket;
            }
        }
    }

    /**
     * @return string
     */
    protected function parseSshConnection()
    {
        return getenv('SSH_CONNECTION') ? reset(explode(' ', getenv('SSH_CONNECTION'))) : DEFAULT_HOST;
    }
}
