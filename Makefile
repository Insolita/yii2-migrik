.PHONY: build up clean cs-fix cli install migrate tests uptests

build:
	@cp -n .env.dist .env
	docker-compose pull
	docker-compose build --pull

clean:
	docker-compose down
	sudo rm -rf tests/runtime/* tests/web/assets/*

up:
	docker-compose up -d

cli:
	docker-compose exec php bash

install:
	docker-compose run --rm php composer install && chmod +x tests/yii

migrate:
	docker-compose run --rm php sh -c 'cd /app/tests && ./yii migrate  --interactive=0'

tests:
	docker-compose run --rm php sh -c 'vendor/bin/phpunit tests/unit'

uptests: up install migrate tests
