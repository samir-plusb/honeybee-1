PROJECT_ROOT=`pwd`
BUILD_DIR=${PROJECT_ROOT}/etc/integration/build/
PHP_ERROR_LOG=`php -i | grep error_log | cut -f '3' -d " "`

help:

	@echo ""
	@echo "########################"
	@echo "#    COMMON TARGETS    #"
	@echo "########################"
	@echo ""
	@echo "cc - Clear the cache directories and set file perms+mode."
	@echo "install - Initially setup a vanilla checkout."
	@echo "tail-logs - Tail all application logs."
	@echo "update - Update the working copy and vendor libs."
	@echo ""
	@echo "#############################"
	@echo "#    DEVELOPMENT TARGETS    #"
	@echo "#############################"
	@echo ""
	@echo "deploy-resources - Generate and deploy script-, style- and binary-packages."
	@echo "install-dev - Initially setup a vanilla development working environment."
	@echo "update-dev - Update the working copy and vendor libs regarding development 'goodies'."
	@echo ""
	@echo "Scafolding"
	@echo "----------"
	@echo "  module - Create and integrate a new module."
	@echo "  module-code - Generate code for an existing module."
	@echo "  remove-module - Remove an existing module."
	@echo ""
	@echo "Php integration and reporting"
	@echo "-----------------------------"
	@echo "  php-code-sniffer - Run the php code-sniffer and publish report."
	@echo "  php-copy-paste-detection - Run the php copy paste detector and publish report."
	@echo "  php-dependencies - Generate dependecies report and graph."
	@echo "  php-docs - Generate php api doc."
	@echo "  php-mess-detection - Run the php mess detector and publish report."
	@echo "  php-metrics - Introspect code and generate source metrics."
	@echo "  php-tests - Run php test suites."
	@echo ""
	@echo "--------------------------"
	@echo "#    INTERNAL TARGETS    #"
	@echo "--------------------------"
	@echo ""
	@echo "config - Generate includes for all modules."
	@echo "generate-autoloads - generate autoloads for vendors/dependencies and libs."
	@echo "install-composer - install composer"
	@echo "install-node-deps - install nodejs dependencies in node_modules folder."
	@echo "install-vendor - install dependencies in vendor folder."
	@echo "install-vendor-dev - install development dependencies in vendor folder."
	@echo "link-project-modules - Symlink custom code into the honeybee submodule and update the local git/ingo/exclude settings."
	@echo "twitter-bootstrap - build twitter-bootstrap with font-awesome."
	@echo "update-node-deps - update nodejs dependencies in node_modules folder."
	@echo "update-vendor - update dependencies in vendor folder."
	@echo "update-vendor-dev - update development dependencies in vendor folder."
	@echo ""
	@exit 0


#
# Common targets
#
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


tail-logs:

	@tail -f "${PHP_ERROR_LOG}" app/log/*.log


#
# Compiling bootstrap and managing resource deployment.
#
twitter-bootstrap: 

	@cp vendor/fortawesome/font-awesome/less/font-awesome.less vendor/twitter/bootstrap/less/
	@sed -i 's/@import "sprites.less"/@import "font-awesome.less"/g' vendor/twitter/bootstrap/less/bootstrap.less
	@sed -i 's/..\/font\/fontawesome-webfont/..\/binaries\/fontawesome-webfont/g' vendor/twitter/bootstrap/less/font-awesome.less
	@export PATH="${PROJECT_ROOT}/node_modules/.bin/:$(PATH)"; cd vendor/twitter/bootstrap; make


deploy-resources:

	@if [ ! -d pub/static/deploy ]; then mkdir pub/static/deploy; fi
	@rm -rf pub/static/deploy/*
	@php bin/deploy-resources.php


#
# Composer and vendor handling
#
install: install-composer install-vendor install-node-deps cc

	@if [ ! -f etc/local/local.config.sh ]; then bin/configure-env --init; fi
	@make twitter-bootstrap
	@make link-project-modules
	@make deploy-resources


install-dev: install-composer install-vendor-dev install-node-deps cc

	@if [ ! -f etc/local/local.config.sh ]; then bin/configure-env --init; fi
	@make twitter-bootstrap
	@make link-project-modules
	@make deploy-resources


update: update-composer update-vendor update-node-deps


update-dev: update-composer update-vendor-dev update-node-deps


install-composer: 

	@if [ -d vendor/agavi/agavi/ ]; then svn revert -R vendor/agavi/agavi/; fi
	@if [ ! -f bin/composer.phar ]; then curl -s http://getcomposer.org/installer \
	| php -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" -- --install-dir=./bin; fi
	-@bin/apply-patches

	
update-composer:

	@bin/composer.phar self-update


install-vendor:

	@php -d allow_url_fopen=1 bin/composer.phar install --no-dev


install-vendor-dev:

	@php -d allow_url_fopen=1 bin/composer.phar install --dev


update-vendor:

	@svn revert -R vendor/agavi/agavi/ || true
	@php -d allow_url_fopen=1 bin/composer.phar update --no-dev
	-@bin/apply-patches


update-vendor-dev:

	@svn revert -R vendor/agavi/agavi/ || true
	@php -d allow_url_fopen=1 bin/composer.phar update --dev
	-@bin/apply-patches


generate-autoloads:

	@php bin/composer.phar dump-autoload


#
# Node module installation and updates
#
install-node-deps:

	@npm install


update-node-deps: install-node-deps

	@npm update


#
# Php integration related tasks.
#
php-tests:

	@nice bin/test --configuration testing/config/phpunit.xml


php-code-sniffer:

	@/bin/mkdir -p ${BUILD_DIR}/logs
	-@vendor/bin/phpcs --report=checkstyle --report-file=${BUILD_DIR}/logs/checkstyle.xml \
		--standard=${PROJECT_ROOT}/etc/coding-standards/BerlinOnline/ruleset.xml \
		--ignore='app/cache*,*Success.php,*Input.php,*Error.php,app/templates/*,*.css,*.js' \
		${PROJECT_ROOT}/app


php-mess-detection:

	@/bin/mkdir -p ${BUILD_DIR}/logs
	-@vendor/bin/phpmd app/ xml codesize,design,naming,unusedcode --reportfile ${BUILD_DIR}/logs/pmd.xml


php-copy-paste-detection:

	@/bin/mkdir -p ${BUILD_DIR}/logs
	-@vendor/bin/phpcpd.php --log-pmd ${BUILD_DIR}/logs/pmd-cpd.xml app/


php-dependencies:

	@/bin/mkdir -p ${BUILD_DIR}/logs
	-@vendor/bin/pdepend --jdepend-xml=${BUILD_DIR}/logs/jdepend.xml \
		--jdepend-chart=${BUILD_DIR}/pdepend/dependencies.svg \
		--overview-pyramid=${BUILD_DIR}/pdepend/overview-pyramid.svg app/


php-docs:

	@/bin/mkdir -p etc/integration/docs/serverside/
	-@vendor/bin/phpdoc.php -c ${PROJECT_ROOT}/app/config/phpdocumentor.xml


php-metrics:

	@/bin/mkdir -p ${BUILD_DIR}/logs
	-@vendor/bin/phploc --log-csv ${BUILD_DIR}/logs/phploc.csv app/


#
# Project modules related tasks.
#
link-project-modules:

	@bin/link-project-modules
	@make config


config:

	-@rm app/config/includes/*
	@php bin/include-configs.php
	@make cc


module:

	@bin/agavi honeybee-module-wizard
	@make config

action:

	@bin/agavi honeybee-action-wizard


remove-module:

	@bin/agavi module-list
	@read -p "Enter module to remove:" module; unlink app/modules/$$module; rm -rf ../project/modules/$$module
	@make link-project-modules
	@make config


module-code:

	@bin/agavi module-list
	@read -p "Enter Module Name:" module; \
    	dator_dir=app/modules/$$module/config/dat0r; \
		vendor/bin/dat0r.console generate $$dator_dir/codegen.ini $$dator_dir/module.xml gen+dep
	@make config
	@curl -XDELETE localhost:9200/
	@echo "\n"


#
# PHONY targets @see http://www.linuxdevcenter.com/pub/a/linux/2002/01/31/make_intro.html?page=2
#
.PHONY: help module cc config install update

# vim: ts=4:sw=4:noexpandtab!:
#
