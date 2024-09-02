setup:
	@docker compose up -d
	@docker compose exec app sh -c 'test -s .env || cp .env.example .env'
	@make composer
	@docker compose exec app php artisan migrate:fresh --seed
	@docker compose exec app php artisan lighthouse:ide-helper

composer:
	@docker compose exec app composer install
