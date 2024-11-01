<?php

namespace Rmate;

// this exists entirely as a way to override realpath for testing, I'm sorry

class RealPath
{
    public static $instance;

    /**
     * @param  string $relPath
     * @return string
     */
    public static function get(string $relPath)
    {
        if (empty(self::$instance)) {
            self::$instance = new Static();
        }

        return self::$instance->getPath($relPath);
    }

    /**
     * @param  string $relPath
     * @return string
     */
    public function getPath(string $relPath)
    {
        return $this->realPath(str_replace('~', getenv('HOME'), $relPath));
    }

    /**
     * @param  string $path
     * @return string
     */
    protected function realPath(string $path)
    {
        return \realpath($path);
    }
}
