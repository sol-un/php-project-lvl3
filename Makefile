start:
	php artisan serve --host 0.0.0.0

setup:
	composer install
	cp -n .env.example .env || true
	php artisan key:gen --ansi
	php artisan migrate
	npm install

deploy:
	git push heroku main

lint:
	composer exec --verbose phpcs -- --standard=PSR12 routes

migrate-redo:
	php artisan migrate:rollback && php artisan migrate