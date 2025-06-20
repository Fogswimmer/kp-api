.PHONY: init init-perms fix-perms uploads jwt composer test clear-cache fixtures

CONTAINER_NAME=symfony

USER_ID=$(shell id -u)
GROUP_ID=$(shell id -g)

init: uploads fix-perms jwt composer

uploads:
	mkdir -p public/uploads
	sudo chmod -R 775 public/uploads
	sudo chown -R $(USER_ID):$(GROUP_ID) public/uploads

jwt:
	docker exec $(CONTAINER_NAME) php bin/console lexik:jwt:generate-keypair --overwrite

composer:
	docker exec $(CONTAINER_NAME) composer install --no-interaction --prefer-dist

fix-perms:
	docker exec $(CONTAINER_NAME) chown -R www-data:www-data var public/uploads
	sudo chown -R $(USER_ID):$(GROUP_ID) .
	sudo chmod -R ug+rwX var public/uploads

test:
	docker-compose -f compose.test.yaml up --build --abort-on-container-exit
clear-cache:
	docker exec symfony php bin/console cache:clear --no-warmup
fixtures:
	docker exec $(CONTAINER_NAME) php bin/console doctrine:fixtures:load --purge-with-truncate --no-interaction
