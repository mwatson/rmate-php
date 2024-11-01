<?php

namespace Rmate;

class Output
{
    protected $out = [];

    /**
     */
    public function __construct()
    {
    }

    /**
     * @param  string $str
     * @return Output
     */
    public function add(string $str) : self
    {
        $this->out[] = $str;
        return $this;
    }

    /**
     * @param  string $str
     * @return Output
     */
    public function addLine(string $str) : self
    {
        $this->add("{$str}\n");
        return $this;
    }

    /**
     * @return Output
     */
    public function flush() : self
    {
        while ($item = array_shift($this->out)) {
            echo $item;
        }

        return $this;
    }
}
