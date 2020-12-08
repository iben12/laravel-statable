build-image:
	docker build -t iben12/laravel-statable:php-$(PHP_VERSION) --build-arg PHP_VERSION=$(PHP_VERSION) .

test:
	docker run --rm iben12/laravel-statable:php-$(PHP_VERSION) vendor/bin/phpunit --coverage-text