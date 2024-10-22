# rmate-php

This is hasty port of [rmate](https://github.com/textmate/rmate) to PHP.

## Why?

Because I wanted to run this on an Automattic WordPress sandbox which I knew it would have PHP. There is a bash version of rmate, so I didn't *need* to do this, but I'll never pass up an opportunity to port something to PHP for no reason.

Also I wanted to be able to quickly remote edit files with Sublime Text because I am set in my ways.

## Requirements

This was primarily tested with [RemoteSubl](https://github.com/randy3k/RemoteSubl) which is the most recently maintained fork of rsub as far as I can tell. So I would recommend installing RemoteSubl if you want to be able to use this functionality.

You also need PHP CLI on your remote server.

## Installation

Locally you will need to forward port 52698. You can do this via your `~/.ssh/config` file:

```
Host your-remote-host.com
    ...
    RemoteForward 52698 localhost:52698
```

Or directly in the ssh command when you connect:

```
ssh -R 52698:localhost:52698 you@your-remote-host.com
```

After that check out this repo on your remote server (put it anywhere you want):

```
git clone git@github.com:mwatson/rmate-php.git
```

## Running / Editing

It can be run by calling the `cli/rmate.php` file:

```
php cli/rmate.php ../path/to/file_to_edit.php
```

I recommend adding an alias to your shell's `.*rc` or `.*profile`:

```
alias rmate="php /full/path/to/rmate-php/src/rmate.php"
```

Once that's done you can load a remote file in Sublime via:

```
rmate ../path/to/file_to_edit.php
```

To call with command line options, put them before the filename:

```
rmate [OPTIONS] ../path/to/file_to_edit.php
```

## Options

There are various command line options you can use:

```
    --host=[HOST]           Connect to host. Use 'auto' to detect the host from SSH. Defaults to localhost.
-s, --unixsocket=[SOCKET]   UNIX socket path. Takes precedence over host/port if the file exists Default ~/.rmate.socket
-p, --port=[PORT]           Port number to use for connection. Defaults to 52698.
-w, --wait                  Wait for file to be closed by Sublime.
-l, --line=[NUMBER]         Place caret on line [NUMBER] after loading file.
-m, --name=[NAME]           The display name shown in Sublime.
-t, --type=[TYPE]           Treat file as having [TYPE].
-f, --force                 Open even if the file is not writable.
-k, --keep-focus            Have Sublime retain window focus after file is closed.
-v, --verbose               Verbose logging messages.
-h, --help                  Show this message.
    --version               Show version.
```

