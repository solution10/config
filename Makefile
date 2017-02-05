
ci:
	php vendor/bin/phpunit -c phpunit.xml.dist --coverage-text
	php vendor/bin/phpcs --standard=PSR2 ./src ./tests
