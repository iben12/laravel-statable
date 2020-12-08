build-image:
	docker build -t iben12/laravel-statable .

test:
	docker run --rm iben12/laravel-statable vendor/bin/phpunit --coverage-text --coverage-clover=coverage.clover