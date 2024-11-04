<?php

namespace Rmate;

class Rmate
{
    /**
     * @param  Settings $settings
     * @param  Output $output
     * @param  Connection $connection
     * @param  CliOptions $cliOptions
     * @param  string $cliFilePath
     */
    public function __construct(
        protected Settings $settings,
        protected Output $output,
        Connection $connection,
        CliOptions $cliOptions,
        string $cliFilePath
    ) {
        if (empty($cliOptions->getUnprocessedCliArgs())) {
            $this->output
                ->addLine("Usage: rmate [OPTIONS] filename")
                ->addLine("See rmate --help for more")
                ->flush();
            return;
        }

        $commands = $this->processCliArgs($cliOptions->getUnprocessedCliArgs());

        if (empty($commands)) {
            return;
        }

        // create handler and connection
        $handler = $this->createCommandHandlerFromConnection($connection);

        $handler->setVerbose($this->settings->verbose);

        if ($this->settings->wait) {
            $handler->connectAndHandleCmds($commands);
        } else {
            // run async, which is really annoying in PHP
            $cliArgs = $cliOptions->getCliArgs();
            // add wait arg so we don't fork bomb ourselves
            array_unshift($cliArgs, '-w');
            $cliArgs = implode(' ', $cliArgs);

            $out = [];
            exec("php {$cliFilePath} {$cliArgs} >> /dev/null 2>&1 & echo $!", $out);
            $pid = (int) $out[0];
            $this->output->addLine("rmate PID: {$pid}")->flush();
        }
    }

    /**
     * @param  array $argsToProcess
     * @return array[Command]
     */
    protected function processCliArgs(array $argsToProcess) : array
    {
        // Parse arguments.
        $commands = [];

        foreach ($argsToProcess as $idx => $path) {
            if ($path == '-') {
                $this->output->addLine("Reading from stdin, press ^D to stop");
            } else {
                if (is_dir($path)) {
                    $this->output->addLine("'{$path}' is a directory! Aborting.")->flush();
                    return [];
                } else if (!$this->settings->force && !is_writable($path)) {
                    $this->output
                        ->addLine("File {$path} is not writable! Use -f or --force to open anyway.")
                        ->flush();
                    return [];
                } else if (!is_writable($path) && $this->settings->verbose) {
                    $this->output->addLine("File {$path} is not writable. Opening anyway.");
                }
            }

            $this->output->flush();

            $cmd = new Command("open");

            if ($path == '-') {
                $cmd->display_name = "{$this->settings->host}:untitled (stdin)";
            }
            if ($path != '-') {
                $cmd->display_name = "{$this->settings->host}:{$path}";
            }
            if (count($this->settings->names) > $idx) {
                $cmd->display_name = $this->settings->names[$idx];
            }
            if ($path != '-') {
                $cmd->real_path = \Rmate\RealPath::get($path);
            }

            $cmd->data_on_save = true;
            $cmd->re_activate = $this->settings->reactivate;
            $cmd->token = $path;

            if (count($this->settings->lines) > $idx) {
                $cmd->selection = $this->settings->lines[$idx];
            }
            if ($path == '-') {
                $cmd->file_type = 'txt';
            }
            if (count($this->settings->types) > $idx) {
                $cmd->file_type = $this->settings->types[$idx];
            }
            if ($path == '-') {
                // read from stdin
                $cmd->readStdin();
            }
            if ($path != '-' && file_exists($path)) {
                $cmd->readFile($path);
            }
            if($path != '-' && !file_exists($path)) {
                $cmd->data = "0";
            }
            
            $commands[] = $cmd;
        }

        return $commands;
    }

    /**
     * @param  Connection $connection
     * @return CommandHandler
     */
    public function createCommandHandlerFromConnection(Connection $connection) : CommandHandler
    {
        return new \Rmate\CommandHandler($connection, $this->output);
    }
}
