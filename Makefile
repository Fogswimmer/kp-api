c.PHONY: build start stop init init-perms jwt composer test clear-cache fixtures restore dump

CONTAINER_NAME=symfony
DB_CONTAINER_NAME=postgres
DUMP_FILE=dump.sql
DB_NAME=symfony
DB_USER=symfony
DB_PASSWORD=secret

USER_ID=$(shell id -u)
GROUP_ID=$(shell id -g)

build:
    docker compose up --build -d
start:
    docker compose up -d
stop:
    docker compose down

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
restore:
    docker cp $(DUMP_FILE) $(DB_CONTAINER_NAME):/$(DUMP_FILE)
    docker exec -i $(DB_CONTAINER_NAME) psql -U $(DB_USER) -d $(DB_NAME) < /$(DUMP_FILE)
dump:
    docker exec $(DB_CONTAINER_NAME) pg_dump -c $(DB_NAME) > $(DUMP_FILE)
es-index:
    docker exec $(CONTAINER_NAME) php bin/console es:index
