### Variables

# Applications
COMPOSER ?= /usr/bin/env composer

### Helpers
all: clean depend

.PHONY: all

### Dependencies
depend:
	${COMPOSER} install --prefer-source --no-interaction

.PHONY: depend

### QA
qa: lint phpstan phpcs phpcpd

lint:
	find ./src -name "*.php" -exec /usr/bin/env php -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0

phploc:
	vendor/bin/phploc src

phpstan:
	vendor/bin/phpstan analyse src --level max

phpcs:
	vendor/bin/phpcs --standard=PSR12 --extensions=php src/

phpcpd:
	vendor/bin/phpcpd src/

.PHONY: qa lint phploc phpstan phpcs phpcpd

### Testing
test:
	php -dxdebug.coverage_enable=1 vendor/bin/phpunit -c phpunit.xml -v --colors --coverage-text
	php vendor/bin/behat

.PHONY: test

### Cleaning
clean:
	rm -rf vendor

.PHONY: clean
