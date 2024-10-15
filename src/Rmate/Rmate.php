<?php

namespace Rmate;

class Rmate {

    protected $settings;

    public function __construct($settings) {
        $this->settings = $settings;
    }

    public function handle_save($socket, $variables, $data) {
        $path = $variables['token'];
        if (is_writable($path) || !file_exists($path)) {
            if ($this->settings->verbose) {
                echo "Saving {$path}\n";
            }

            $backup_path = "{$path}~";
            while (file_exists($backup_path)) {
                $backup_path = "{$backup_path}~";
            }                
            if (file_exists($path)) {
                copy($path, $backup_path);
            }
            $saved = file_put_contents($path, $data);
            if (file_exists($backup_path)) {
                unlink($backup_path);
            }

            if ($saved === false) {
                // TODO We probably want some way to notify the server app that the save failed
                if ($this->settings->verbose) {
                    echo "Save failed! {$e->getMessage()}\n";
                }
            }
        } else {
            if ($this->settings->verbose) {
                echo "Skipping save, file not writable.\n";
            }
        }
    }

    public function handle_close($socket, $variables, $data) {
        $path = $variables['token'];
        if ($this->settings->verbose) {
            echo "Closed {$path}\n";
        }
    }

    public function handle_cmd($socket) {
        $cmd = trim(fgets($socket));

        $variables = [];
        $data = "";

        while ($line = fgets($socket)) {
            $line = trim($line);
            if (empty($line)) {
                break;
            }
          
            [ $name, $value ] = explode(': ', $line);
            $variables[$name] = $value;
          
            if ($name == "data") {
                $data = fread($socket, intval($value));
            }
        }
        unset($variables['data']);

        switch ($cmd) {
            case "save":
                $this->handle_save($socket, $variables, $data);
                break;
            case "close";
                $this->handle_close($socket, $variables, $data);
                break;
            default:
                echo "Received unknown command \"{$cmd}\", exiting.\n";
                die;
        }
    }

    public function connect_and_handle_cmds($host, $port, $unixsocketpath, $cmds) {
        $socket = null;
        $errno = null;
        $errstr = null;
        if (!empty($unixsocketpath)) {
            $unixsocketpath = realpath($unixsocketpath);
        }
        if (empty($unixsocketpath) || !file_exists($unixsocketpath)) {
            if ($this->settings->verbose) {
                echo "Using TCP socket to connect: '{$host}:{$port}'\n";
            }
            $socket = stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr);
            //$socket = fsockopen("tcp://{$host}", $port, $errno, $errstr);
            if (!$socket) {
                echo "Error connecting to '{$host}:{$port}': [{$errno}] {$errstr}\n";
                die;
            }
        } else {
            if ($this->settings->verbose) {
                echo "Using UNIX socket to connect: '{$unixsocketpath}'";
            }
            $socket = stream_socket_client("unix://{$unixsocketpath}", $errno, $errstr);
            if (!$socket) {
                echo "Error using socket {$unixsocketpath}: [{$errno}] {$errstr}\n";
                die;
            }
        }
        $server_info = trim(fgets($socket));
        if ($this->settings->verbose) {
            echo "Connect: '{$server_info}'\n";
        }

        foreach ($cmds as $cmd) {
            $cmd->send($socket);
        }

        fsockwrite($socket, ".");
        while (!feof($socket)) {
            $this->handle_cmd($socket);
        }
        fclose($socket);
        if ($this->settings->verbose) {
            echo "Done\n";
        }
    }
}

