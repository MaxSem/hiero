.PHONY: all

all: test phpcs phpstan composer-validate

.PHONY: test
test:
	vendor/bin/phpunit tests

phpcs:
	 vendor/bin/phpcs -s --standard=phpcs.xml

phpcbf:
	 vendor/bin/phpcbf --standard=phpcs.xml

phpstan:
	vendor/bin/phpstan

composer-validate:
	composer validate
