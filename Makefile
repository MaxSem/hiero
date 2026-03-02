.PHONY: test

test:
	vendor/bin/phpunit test

phpcs:
	 vendor/bin/phpcs --standard=phpcs.xml

phpcbf:
	 vendor/bin/phpcbf --standard=phpcs.xml
