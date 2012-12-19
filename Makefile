PROJECT_ROOT=`pwd`

help:

	@echo "COMMON"
	@echo "  cc - clear the cache directories and set file perms+mode."
	@echo "  tail-logs - tail all application logs."
	@echo "  install - initially setup a vanilla checkout."
	@echo "  update - update the working copy and vendor libs."
	@echo ""
	@echo "DEVELOPMENT"
	@echo "  Scafolding"
	@echo "    module - Create and integrate a new module."
	@echo "    module-code - Generate code for a certain module."
	@echo "    config - Generate includes for all modules."
	@echo "    deploy-resources - Generate and deploy js css and assets."
	@echo "  Php"
	@echo "    test - run php test suites."
	@echo "    phpcs - run the php code-sniffer and publish report."
	@echo "  Scripts:"
	@echo "    js-specs - run vows scenarios with spec output to test the project's js code."
	@echo "    js-xunit - run vows scenarios with xunit output to test the project's js code."
	@echo "    js-docs - generate api doc for the project's js code."
	@echo "  Styles:"
	@echo "    lessc - compile less files to css directory."
	@echo "    lessw - compile less files to css directory and watch less files for changes to then auto-compile."
	@echo ""
	@echo "INTERNAL"
	@echo "  install-composer - install composer"
	@echo "  install-vendor - install dependencies in vendor folder."
	@echo "  update-vendor - update dependencies in vendor folder."
	@echo "  install-node-deps - install nodejs dependencies in node_modules folder."
	@echo "  update-node-deps - update nodejs dependencies in node_modules folder."
	@echo "  generate-autoloads - generate autoloads for vendors/dependencies and libs."
	@echo "  twitter-bootstrap - build twitter-bootstrap with font-awesome."
	@exit 0


cc:

	@if [ ! -d app/cache ]; then mkdir -p app/cache; fi
	@if [ ! -d app/log ]; then mkdir -p app/log; fi
	@if [ ! -d data/assets ]; then mkdir -p data/assets; fi
	@chmod 775 app/cache
	@chmod 775 data/assets
	@chmod 775 app/log
	@rm -rf app/cache/*
	@echo "-> ensured consistency for: app/cache(cleared), app/log and data/assets."

	@if [ ! -d pub/static/cache ]; then mkdir pub/static/cache; fi
	@chmod 775 pub/static/cache
	@rm -rf pub/static/cache/*
	@echo "-> cleared public resources cache."

	@make generate-autoloads


config: cc

	-@rm app/config/includes/*
	@php bin/include_configs.php
	@make cc


install: install-vendor install-node-deps cc

	@if [ ! -f etc/local/local.config.sh ]; then bin/configure-env --init; fi
	@make twitter-bootstrap


update: update-composer update-vendor update-node-deps cc


tail-logs:

	@tail -f app/log/*.log


generate-autoloads:

	@php bin/composer.phar dump-autoload


twitter-bootstrap: 

	@cp vendor/fortawesome/font-awesome/less/font-awesome.less vendor/twitter/bootstrap/less/
	@sed -i 's/@import "sprites.less"/@import "font-awesome.less"/g' vendor/twitter/bootstrap/less/bootstrap.less
	@sed -i 's/..\/font\/fontawesome-webfont/..\/binaries\/fontawesome-webfont/g' vendor/twitter/bootstrap/less/font-awesome.less
	@export PATH="${PROJECT_ROOT}/node_modules/.bin/:$(PATH)"; cd vendor/twitter/bootstrap; make


deploy-resources:

	@if [ ! -d pub/static/deploy ]; then mkdir pub/static/deploy; fi
	@rm -rf pub/static/deploy/*
	@php bin/deploy_resources.php


install-composer: 

	@if [ -d vendor/agavi/agavi/ ]; then svn revert -R vendor/agavi/agavi/; fi
	@if [ ! -f bin/composer.phar ]; then curl -s http://getcomposer.org/installer | php -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" -- --install-dir=./bin; fi
	@bin/apply_patches
	

update-composer:
	@bin/composer.phar self-update


install-vendor: install-composer

	@php -d allow_url_fopen=1 bin/composer.phar install


update-vendor: install-vendor

	@svn revert -R vendor/agavi/agavi/ || true
	@php -d allow_url_fopen=1 bin/composer.phar update
	@bin/apply_patches


install-node-deps:

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


module:

	@bin/agavi honeybee-module-wizard
	@make config


module-code:

	@bin/agavi module-list
	@read -p "Enter Module Name:" module; \
    	dator_dir=app/modules/$$module/config/dat0r; \
		vendor/bin/dat0r.console generate $$dator_dir/codegen.ini $$dator_dir/module.xml gen+dep
	@make config
	@curl -XDELETE localhost:9200/_all


.PHONY: help module module-code lessw lessc jsdoc js-xunit js-specs phpdoc phpcs test twitter-bootstrap cc config install update

# vim: ts=4:sw=4:noexpandtab!:
#