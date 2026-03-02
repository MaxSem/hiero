.PHONY: test
.PHONY: grammar

test:
	vendor/bin/phpunit test

#grammar:
#	vendor/bin/phpyacc -m grammar/Parser.template.php grammar/grammar.y
#	mv grammar/grammar. src/Parse/Parser_.php
