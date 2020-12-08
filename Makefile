build-image:
	docker build -t iben12/laravel-statable:php-$(PHP_VERSION) --build-arg PHP_VERSION=$(PHP_VERSION) .

test:
	docker build -t iben12/laravel-statable:php-$(PHP_VERSION) --build-arg PHP_VERSION=$(PHP_VERSION) .
	docker run --rm iben12/laravel-statable:php-$(PHP_VERSION) vendor/bin/phpunit --coverage-text

action:
	act -P ubuntu-latest=shivammathur/node:latest -s GITHUB_TOKEN=${GITHUB_TOKEN}