help:

	@echo "Possible targets:"
	@echo "  cc - clear cache"
	@echo "  tail-logs - tail the application.log"
	@echo "  update-vendor - update libraries in vendor folder"
	@echo "Hidden targets:"
	@echo "  install-composer - install composer"
	@echo "  generate-autoloads - generate autoloads for vendors and libs"
	@exit 0

install-composer:

	@if [ ! -f bin/composer.phar ]; then curl -s http://getcomposer.org/installer | php -n -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" -- --install-dir=./bin; fi

generate-autoloads:

	@make install-composer	
	@php bin/composer.phar dump-autoload

cc:
	@if [ ! -d app/cache ]; then mkdir app/cache; fi
	@if [ ! -d pub/js/_cache ]; then mkdir pub/js/_cache; fi
	@if [ ! -d pub/css/_cache ]; then mkdir pub/css/_cache; fi
	@if [ ! -d app/log ]; then mkdir app/log; fi
	@rm -rf app/cache/*
	@chmod 777 app/cache
	@chmod 777 app/log
	@echo "app/cache cleared"
	@rm -rf pub/js/_cache/*
	@chmod 777 pub/js/_cache
	@echo "pub/js/_cache cleared"
	@rm -rf pub/css/_cache/*
	@chmod 777 pub/css/_cache
	@echo "pub/css/_cache cleared"
	@make generate-autoloads

tail-logs:
	@tail -f app/log/*.log

update-vendor:
		
	@svn revert -R vendor/agavi/agavi/ || true
	@make install-composer	
	@php -d allow_url_fopen=1 bin/composer.phar update && php -d allow_url_fopen=1 bin/composer.phar install
	@make cc

.PHONY: help

# vim: ts=4:sw=4:noexpandtab!:
#
