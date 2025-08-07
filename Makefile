c.PHONY: init init-perms jwt composer test clear-cache fixtures

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
test:
	docker exec $(CONTAINER_NAME) php bin/phpunit
clear-cache:
	docker exec symfony php bin/console cache:clear --no-warmup
fixtures:
	docker exec $(CONTAINER_NAME) php bin/console doctrine:fixtures:load --purge-with-truncate --no-interaction
