<?php

namespace Rmate;

class CommandHandler
{
    protected $verbose = false;

    /**
     * @param  Connection $connection
     * @param  Output $output
     */
    public function __construct(
        protected Connection $connection,
        protected Output $output
    ) {
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
            $this->output->addLine(sprintf(
                "Using %s socket to connect: '%s'",
                $this->connection->getType(),
                $this->connection->getConnectAddr()
            ));
        }

        try {
            $this->connection->connect();
        } catch (\Exception $e) {
            $this->output->addLine($e->getMessage())->flush();
            // TODO: handle exit elsewhere
            exit;
        }

        $serverInfo = trim($this->connection->nextLine());
        if ($this->verbose) {
            $this->output->addLine("Connect: '{$serverInfo}'")->flush();
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
            $this->output->addLine("Done");
        }

        $this->output->flush();
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
                $this->output->addLine("Received unknown command '{$cmd}', exiting.")->flush();
                // TODO: handle exit elsewhere
                exit;
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
                $this->output->addLine("Saving {$path}");
            }

            $backup_path = "{$path}~";
            while (file_exists($backup_path)) {
                $backup_path .= '~';
            }                
            if (file_exists($path)) {
                copy($path, $backup_path);
            }
            //$saved = file_put_contents($path, $data);
            $fp = fopen($path, 'w');
            $saved = fwrite($fp, $data);
            if (file_exists($backup_path)) {
                unlink($backup_path);
            }

            if ($saved === false) {
                // TODO We probably want some way to notify the server app that the save failed
                if ($this->verbose) {
                    $this->output->addLine("Save failed! {$e->getMessage()}");
                }
            }
        } else {
            if ($this->verbose) {
                $this->output->addLine("Skipping save, file not writable.");
            }
        }

        $this->output->flush();
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
            $this->output->addLine("Closed {$path}")->flush();
        }
    }
}
