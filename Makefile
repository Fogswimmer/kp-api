.PHONY: init-perms jwt uploads fix-perms init-all

uploads:
	mkdir -p public/uploads
	sudo chmod -R 775 public/uploads

fix-perms:
	docker exec symfony chown -R www-data:www-data var public/uploads
	sudo chown -R $(shell id -u):$(shell id -g) .
	sudo chmod -R ug+rwX .

jwt:
	docker exec symfony php bin/console lexik:jwt:generate-keypair
init-all: uploads fix-perms jwt
