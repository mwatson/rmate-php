<?php
/**
 * This is only a PHP file because I was considering automatically adding
 * the alias to the user's profile, but then decided against it.
 * 
 * The non-color coded string is:
 * 'alias rmate="php ' . __DIR__ . '/cli/rmate.php"';
 * 
 */

echo "Add the following to your shell profile:\n\n";
echo "  \033[95malias \033[36mrmate\033[33m=\033[32m\"php " . __DIR__ . "/cli/rmate.php\"\033[0m\n\n";
exit(0);
