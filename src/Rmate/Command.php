<?php

namespace Rmate;

class Command {
    protected $command;
    protected $variables;
    protected $data;
    protected $size;

    /**
     * @param  string $name
     */
    public function __construct(string $name) {
        $this->command = $name;
        $this->variables = [];
        $this->data = null;
        $this->size = null;
    }

    /**
     * @param  string $name
     * @param  mixed $value
     */
    public function __set($name, $value) 
    {
        $this->variables[$name] = $value;
    }

    /**
     * Read a file into memory
     * @param  string $path
     * @return void
     */
    public function readFile($path) : void
    {
        $this->size = filesize($path);
        $this->data = file_get_contents($path);
    }

    /**
     * Read from stdin until eof (^D)
     * @return void
     */
    public function readStdin() : void
    {
        $fp = fopen("php://stdin", "r");
        while (!feof($fp)) {
            $this->data .= fgets($fp);
        }
        fclose($fp);
        $this->size = strlen($this->data);
    }

    /**
     * Send data
     * @param  Connection $connection
     * @return void
     */
    public function send(Connection $connection) : void
    {
        $connection->write($this->command);
        foreach ($this->variables as $name => $value) {
            if ($value === true) {
                $value = 'yes';
            }
            $name = str_replace('_', '-', $name);
            $connection->write("{$name}:{$value}");
        }
        if ($this->data) {
            $connection->write("data:{$this->size}");
            $connection->write($this->data);
        }
        
        $connection->write("");
    }
}
