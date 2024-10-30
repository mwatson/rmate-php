<?php

namespace Rmate;

class CommandHandler
{
    protected $connection;

    protected $verbose = false;

    /**
     * @param  Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param  bool $set
     * @return self
     */
    public function setVerbose(bool $set) : self
    {
        $this->verbose = $set;
        return $this;
    }

    /**
     * @param  array[Command] $cmds
     * @return void
     */
    public function connectAndHandleCmds(array $cmds) : void
    {
        if ($this->verbose) {
            echo sprintf(
                "Using %s socket to connect: '%s'\n",
                $this->connection->getType(),
                $this->connection->getConnectAddr()
            );
        }

        if (!$this->connection->connect()) {
            die;
        }

        $serverInfo = trim($this->connection->nextLine());
        if ($this->verbose) {
            echo "Connect: '{$serverInfo}'\n";
        }

        foreach ($cmds as $cmd) {
            $cmd->send($this->connection);
        }

        $this->connection->write(".");
        while (!$this->connection->isEof()) {
            $this->handleCmd();
        }
        $this->connection->close();
        if ($this->verbose) {
            echo "Done\n";
        }
    }

    /**
     * @param  Connection $connection
     * @return void
     */
    protected function handleCmd() : void
    {
        $cmd = trim($this->connection->nextLine());

        if (!strlen($cmd)) {
            return;
        }

        $variables = [];
        $data = "";

        while ($line = $this->connection->nextLine()) {
            $line = trim($line);
            if (empty($line)) {
                break;
            }
          
            [ $name, $value ] = explode(': ', $line);
            $variables[$name] = $value;
          
            if ($name == "data") {
                $data = $this->connection->read(intval($value));
            }
        }
        unset($variables['data']);

        switch ($cmd) {
            case 'save':
                $this->handleSave($variables, $data);
                break;
            case 'close';
                $this->handleClose($variables, $data);
                break;
            default:
                echo "Received unknown command '{$cmd}', exiting.\n";
                die;
        }
    }

    /**
     * @param  array $variables
     * @param  string $data
     * @return void
     */
    protected function handleSave(array $variables, string $data) : void
    {
        $path = $variables['token'] ?? "";
        if (is_writable($path) || !file_exists($path)) {
            if ($this->verbose) {
                echo "Saving {$path}\n";
            }

            $backup_path = "{$path}~";
            while (file_exists($backup_path)) {
                $backup_path .= '~';
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
                if ($this->verbose) {
                    echo "Save failed! {$e->getMessage()}\n";
                }
            }
        } else {
            if ($this->verbose) {
                echo "Skipping save, file not writable.\n";
            }
        }
    }

    /**
     * @param  array $variables
     * @param  string $data
     * @return void
     */
    protected function handleClose(array $variables, string $data) : void
    {
        $path = $variables['token'] ?? "";
        if ($this->verbose) {
            echo "Closed {$path}\n";
        }
    }
}
