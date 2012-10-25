PROJECT_ROOT=`pwd`

help:

	@echo "Common targets:"
	@echo "  cc - clear the cache directories and set file perms+mode."
	@echo "  tail-logs - tail all application logs."
	@echo "  install - initially setup a vanilla checkout."
	@echo "  update - update the working copy and vendor libs."
	@echo "Development targets:"
	@echo "  PHP"
	@echo "    test - run php test suites."
	@echo "    phpcs - run the php code-sniffer and publish report."
	@echo "  Scripts"
	@echo "    js-specs - run vows scenarios with spec output to test the project's js code."
	@echo "    js-xunit - run vows scenarios with xunit output to test the project's js code."
	@echo "    js-docs - generate api doc for the project's js code."
	@echo "  Styles"
	@echo "    lessc - compile less files to css directory."
	@echo "    lessw - compile less files to css directory and watch less files for changes to then auto-compile."
	@echo "Internal targets:"
	@echo "  install-composer - install composer"
	@echo "  install-vendor - install dependencies in vendor folder."
	@echo "  update-vendor - update dependencies in vendor folder."
	@echo "  install-node-deps - install nodejs dependencies in node_modules folder."
	@echo "  update-node-deps - update nodejs dependencies in node_modules folder."
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


install: install-vendor install-node-deps cc

	@bin/configure-env --init


tail-logs:

	@tail -f app/log/*.log


update: update-vendor update-node-deps cc


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
	@rm -rf vendor/phpdocumentor/phpdocumentor/data/templates/*
	@ln -s ${PROJECT_ROOT}/data/templates/* vendor/phpdocumentor/phpdocumentor/data/templates/


install-node-deps: install-composer

	@npm install


update-node-deps: install-node-deps

	@npm update


test:

	@nice bin/test --configuration testing/config/phpunit.xml


phpcs:

	@/bin/mkdir -p etc/integration/build/logs
	-@vendor/bin/phpcs --report=checkstyle --report-file=${PROJECT_ROOT}/etc/integration/build/logs/checkstyle.xml --standard=${PROJECT_ROOT}/etc/coding-standards/BerlinOnline/ruleset.xml --ignore='app/cache*,*Success.php,*Input.php,*Error.php,app/templates/*' ${PROJECT_ROOT}/app

phpdoc:

	@/bin/mkdir -p etc/integration/docs/serverside/
	@vendor/bin/phpdoc.php --config ${PROJECT_ROOT}/app/config/phpdocumentor.xml


js-specs:

	@bin/test-js --spec


js-xunit:

	@/bin/rm -rf etc/integration/build/logs/clientside.xml
	@/bin/mkdir -p etc/integration/build/logs
	@bin/test-js --xunit | cat > etc/integration/build/logs/clientside.xml


jsdoc:

	@/bin/mkdir -p etc/integration/docs/clientside
	@bin/jsdoc pub/js/midas --output etc/integration/docs/clientside/


lessc:

	@bin/lessc -d pub/less


lessw: lessc

	@bin/lessc -d pub/less -watch


.PHONY: help

# vim: ts=4:sw=4:noexpandtab!:
#