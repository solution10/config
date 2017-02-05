
ci:
	php vendor/bin/phpunit -c phpunit.xml.dist
	php vendor/bin/phpcs --standard=PSR2 ./src ./tests
