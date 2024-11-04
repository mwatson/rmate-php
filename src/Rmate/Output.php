<?php

namespace Rmate;

// Very basic output buffer

class Output
{
    /**
     */
    public function __construct(protected array $out = [])
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
