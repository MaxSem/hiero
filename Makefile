.PHONY: all

all: test phpcs phpstan composer-validate

.PHONY: test
test:
	export DUMP_TEST_IMAGES=1 && vendor/bin/phpunit tests

phpcs:
	 vendor/bin/phpcs -s --standard=phpcs.xml

phpcbf:
	 vendor/bin/phpcbf --standard=phpcs.xml

phpstan:
	vendor/bin/phpstan

composer-validate:
	composer validate --strict

regenerate-test-data:
	bin/build-font.php tests/Render/data/font-src tests/Render/data/font
