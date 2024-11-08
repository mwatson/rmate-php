<?php

namespace Rmate;

class CliOptions
{
    protected $options;

    protected $unprocessedArgs;

    protected $defaults = [
        'host' => DEFAULT_HOST,
        'port' => DEFAULT_PORT,
        'unixsocket' => DEFAULT_SOCKET,
    ];

    /**
     * @param array $cliArgs
     */
    public function __construct(protected array $cliArgs)
    {
        $this->options = [
            [
                'option' => 'host',
                'val_display' => '[HOST]',
                'short' => '',
                'type' => 'value',
                'description' => [
                    "Connect to host.",
                    "Use 'auto' to detect the host from SSH.",
                    "Defaults to {$this->defaults['host']}.",
                ],
                'cb' => function(Settings $settings, string $val) {
                    $settings->host = $val;
                },
            ],
            [
                'option' => 'unixsocket',
                'val_display' => '[SOCKET]',
                'short' => 's',
                'type' => 'value',
                'description' => [
                    "UNIX socket path.",
                    "Takes precedence over host/port if the file exists",
                    "Default {$this->defaults['unixsocket']}",
                ],
                'cb' => function(Settings $settings, string $val) {
                    $settings->unixsocket = $val;
                },
            ],
            [
                'option' => 'port',
                'val_display' => '[PORT]',
                'short' => 'p',
                'type' => 'value',
                'description' => [
                    "Port number to use for connection.",
                    "Defaults to {$this->defaults['port']}.",
                ],
                'cb' => function(Settings $settings, int $val) {
                    $settings->port = $val;
                },
            ],
            [
                'option' => 'wait',
                'val_display' => '',
                'short' => 'w',
                'type' => 'flag',
                'description' => [
                    "Wait for file to be closed by Sublime.",
                ],
                'cb' => function(Settings $settings) {
                    $settings->wait = true;
                },
            ],
            [
                'option' => 'line',
                'val_display' => '[NUMBER]',
                'short' => 'l',
                'type' => 'value',
                'description' => [
                    "Place caret on line [NUMBER] after loading file.",
                ],
                'cb' => function(Settings $settings, int $val) {
                    $settings->lines[] = $val;
                },
            ],
            [
                'option' => 'name',
                'val_display' => '[NAME]',
                'short' => 'm',
                'type' => 'value',
                'description' => [
                    "The display name shown in Sublime.",
                ],
                'cb' => function(Settings $settings, int $val) {
                    $settings->names[] = $val;
                },
            ],
            [
                'option' => 'type',
                'val_display' => '[TYPE]',
                'short' => 't',
                'type' => 'value',
                'description' => [
                    "Treat file as having [TYPE].",
                ],
                'cb' => function(Settings $settings, string $val) {
                    $settings->types[] = $val;
                },
            ],
            [
                'option' => 'force',
                'val_display' => '',
                'short' => 'f',
                'type' => 'flag',
                'description' => [
                    "Open even if the file is not writable.",
                ],
                'cb' => function(Settings $settings) {
                    $settings->force = true;
                },
            ],
            [
                'option' => 'keep-focus',
                'val_display' => '',
                'short' => 'k',
                'type' => 'flag',
                'description' => [
                    "Have Sublime retain window focus after file is closed.",
                ],
                'cb' => function(Settings $settings) {
                    $settings->reactivate = true;
                },
            ],
            [
                'option' => 'verbose',
                'val_display' => '',
                'short' => 'v',
                'type' => 'flag',
                'description' => [
                    "Verbose logging messages.",
                ],
                'cb' => function(Settings $settings) {
                    $settings->verbose = true;
                },
            ],
            [
                'option' => 'help',
                'val_display' => '',
                'short' => 'h',
                'type' => 'info',
                'description' => [
                    "Show this message.",
                ],
                'cb' => function(Settings $settings, Output $output, array $options) {
                    $maxLen = 0;
                    foreach ($options as $option) {
                        $len = strlen($option['val_display']) + strlen($option['option']) + 3;
                        if ($len > $maxLen) {
                            $maxLen = $len;
                        }
                    }
                    foreach ($options as $option) {
                        if ($option['short']) {
                            $output->add("-{$option['short']}, ");
                        } else {
                            $output->add("    ");
                        }

                        $optStr = "--{$option['option']}";
                        if (!empty($option['val_display'])) {
                            $optStr .= "={$option['val_display']}";
                        }
                        $output
                            ->add(str_pad($optStr, $maxLen + 2, ' ', STR_PAD_RIGHT))
                            ->add(' ')
                            ->addLine(implode(' ', $option['description']));
                    }
                },
            ],
            [
                'option' => 'version',
                'val_display' => '',
                'short' => '',
                'type' => 'info',
                'description' => [
                    "Show version.",
                ],
                'cb' => function(Settings $settings, Output $output, array $options) {
                    $output->addLine(VERSION_STRING);
                },
            ],
        ];
    }

    /**
     * @return array
     */
    public function getCliArgs() : array
    {
        return $this->cliArgs;
    }

    /**
     * @return array
     */
    public function getUnprocessedCliArgs() : array
    {
        return $this->unprocessedArgs;
    }

    /**
     * @param  Settings $settings
     * @return void
     */
    public function parseCliOptions(Settings $settings, Output $output) : void
    {
        $i = 0;
        foreach ($this->cliArgs as $i => $arg) {
            $opt = [];
            if (strpos($arg, '--') === 0) {
                $opt = $this->retrieveCliOption(substr($arg, 2), 'option');
            } else if (strpos($arg, '-') === 0 && strlen($arg) > 1) {
                $opt = $this->retrieveCliOption(substr($arg, 1), 'short');
            } else {
                // we (probably) found the file(s) portion
                break;
            }

            if (empty($opt)) {
                $output->addLine("Unknown option '{$arg}'")->flush();
                die;
            }

            // run the callback
            if ($opt['type'] == 'value') {
                $parts = explode('=', $arg);
                $opt['cb']($settings, $parts[1]);
            } else if ($opt['type'] == 'flag') {
                $opt['cb']($settings);
            } else if ($opt['type'] == 'info') {
                $opt['cb']($settings, $output, $this->options);
                $output->flush();
                exit(0);
            }
        }

        $this->unprocessedArgs = array_values(
            array_slice($this->cliArgs, $i)
        );
    }

    /**
     * @param  string $search
     * @param  string $type
     * @return array
     */
    protected function retrieveCliOption(string $search, $type) : array
    {
        foreach ($this->options as $option) {
            if (strlen($option[$type]) && strpos($search, $option[$type]) === 0) {
                return $option;
            }
        }

        return [];
    }
}
