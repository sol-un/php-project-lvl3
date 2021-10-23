start:
	php artisan serve --host 0.0.0.0

setup:
	composer install
	npm install

deploy:
	git push heroku main

lint:
	composer exec --verbose phpcs -- --standard=PSR12 routes