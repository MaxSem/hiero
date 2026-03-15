.PHONY: test

test:
	vendor/bin/phpunit test

phpcs:
	 vendor/bin/phpcs -s --standard=phpcs.xml

phpcbf:
	 vendor/bin/phpcbf --standard=phpcs.xml

phpstan:
	vendor/bin/phpstan
