PHPUNIT=vendor/bin/phpunit
COVERAGE=coverage/

tests: tests/* src/* cli/* ; $(PHPUNIT) tests/

coverage: tests/* src/* cli/* ; $(PHPUNIT) --coverage-html $(COVERAGE) tests/

clean: ; @rm -rf $(COVERAGE) .phpunit.cache/

alias: ; @php rmate-alias.php
