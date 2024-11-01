PHPUNIT=vendor/bin/phpunit

tests: tests/* src/* cli/* ; $(PHPUNIT) --bootstrap cli/bootstrap.php tests/

alias: ; @php rmate-alias.php
