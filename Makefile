help:

	@echo "Possible targets:"
	@echo "  cc - clear the cache directories and set file perms+mode."
	@echo "  tail-logs - tail all application logs."
	@echo "  install - initially setup a vanilla checkout."
	@echo "  update - update the working copy and vendor libs."
	@echo "Hidden targets:"
	@echo "  install-composer - install composer"
	@echo "  install-vendor - install dependencies in vendor folder."
	@echo "  update-vendor - update dependencies in vendor folder."
	@echo "  generate-autoloads - generate autoloads for vendors/dependencies and libs."
	@exit 0

cc:
	@if [ ! -d app/cache ]; then mkdir app/cache; fi
	@if [ ! -d pub/js/_cache ]; then mkdir pub/js/_cache; fi
	@if [ ! -d pub/css/_cache ]; then mkdir pub/css/_cache; fi
	@if [ ! -d app/log ]; then mkdir app/log; fi
	@if [ ! -d data/assets ]; then mkdir data/assets; fi
	@rm -rf app/cache/*
	@chmod 777 app/cache
	@chmod 777 data/assets
	@chmod 777 app/log
	@echo "app/cache cleared"
	@rm -rf pub/js/_cache/*
	@chmod 777 pub/js/_cache
	@echo "pub/js/_cache cleared"
	@rm -rf pub/css/_cache/*
	@chmod 777 pub/css/_cache
	@echo "pub/css/_cache cleared"
	@make generate-autoloads

install: install-vendor cc

tail-logs:
	@tail -f app/log/*.log

update: update-vendor cc

generate-autoloads:

	@make install-composer	
	@php bin/composer.phar dump-autoload

install-composer:
	@if [ -d vendor/agavi/agavi/ ]; then svn revert -R vendor/agavi/agavi/; fi
	@if [ ! -f bin/composer.phar ]; then curl -s http://getcomposer.org/installer | php -n -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" -- --install-dir=./bin; fi
	@bin/apply_patches
	
install-vendor: install-composer

	@php -d allow_url_fopen=1 bin/composer.phar install

update-vendor: install-vendor
	@svn revert -R vendor/agavi/agavi/ || true
	@php -d allow_url_fopen=1 bin/composer.phar update
	@bin/apply_patches

.PHONY: help

# vim: ts=4:sw=4:noexpandtab!:
#
