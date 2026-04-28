DC=docker compose
PHP=$(DC) exec php

up:
	$(DC) up

up-elk:
	$(DC) -f docker-compose.yaml -f docker-compose.elk.yaml up

build:
	$(DC) up --build

down:
	$(DC) down

bash:
	$(PHP) bash

migrate:
	$(PHP) php bin/console doctrine:migrations:migrate

cache-clear:
	$(PHP) php bin/console cache:clear

init:
	$(DC) up -d --build
	$(PHP) composer install
	$(PHP) php bin/console doctrine:database:create --if-not-exists
	$(PHP) php bin/console doctrine:migrations:migrate --no-interaction
