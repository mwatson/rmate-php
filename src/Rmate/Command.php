<?php

namespace Rmate;

class Command {
    protected $command;
    protected $variables;
    protected $data;
    protected $size;

    public function __construct($name) {
        $this->command = $name;
        $this->variables = [];
        $this->data = null;
        $this->size = null;
    }

    public function __set($name, $value) {
        $this->variables[$name] = $value;
    }

    public function read_file($path) {
        $this->size = filesize($path);
        $this->data = file_get_contents($path);
    }

    public function read_stdin() {
        $this->data = fopen("stdin://", "");
        $this->size = strlen($this->data) * 8;
    }

    public function send($socket) {
        fsockwrite($socket, $this->command);
        foreach ($this->variables as $name => $value) {
            if ($value === true) {
                $value = 'yes';
            }
            $name = str_replace('_', '-', $name);
            fsockwrite($socket, "{$name}:{$value}");
        }
        if ($this->data) {
            fsockwrite($socket, "data:{$this->size}");
            fsockwrite($socket, $this->data);
        }
        
        fsockwrite($socket, "");
    }
}
