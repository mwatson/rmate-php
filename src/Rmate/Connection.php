<?php

namespace Rmate;

class Connection
{
    protected $socket = null;

    protected $connectAddr = '';

    protected $type = '';

    /**
     * @param  string $host
     * @param  int $port
     * @param  string $unixSocketPath
     */
    public function __construct(
        string $host = DEFAULT_HOST,
        int $port = DEFAULT_PORT,
        string $unixSocketPath = ''
    ) {
        if (!empty($unixSocketPath)) {
            $unixSocketPath = RealPath::get($unixSocketPath);
        }

        if (empty($unixSocketPath) || !file_exists($unixSocketPath)) {
            $this->type = 'TCP';
            $this->connectAddr = "{$host}:{$port}";
        } else {
            $this->type = 'UNIX';
            $this->connectAddr = $unixSocketPath;
        }
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getConnectAddr()
    {
        return $this->connectAddr;
    }

    /**
     * @return void
     */
    public function connect() : void
    {
        $errno = null;
        $errstr = null;
        $fullAddr = strtolower($this->type) . "://{$this->connectAddr}";
        $this->socket = stream_socket_client($fullAddr, $errno, $errstr);

        if (!$this->socket) {
            throw new \Exception("Error connecting '{$this->connectAddr}': [{$errno}] {$errstr}");
        }
    }

    /**
     * @return string|bool
     */
    public function nextLine() : string|bool
    {
        return fgets($this->socket);
    }

    /**
     * @param  int $length
     * @return string|bool
     */
    public function read(int $length) : string|bool
    {
        return fread($this->socket, $length);
    }

    /**
     * @param  string $data
     * @return int|bool
     */
    public function write(string $data) : int|bool
    {
        return fwrite($this->socket, "{$data}\n");
    }

    /**
     * @return bool
     */
    public function isEof() : bool
    {
        return feof($this->socket);
    }

    /**
     * @return bool
     */
    public function close() : bool
    {
        return fclose($this->socket);
    }
}
