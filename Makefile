.DEFAULT_GOAL := help
.SILENT:

## Colors
COLOR_RESET   = \033[0m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

PLATFORM_ROOT=/srv
PLATFORM_RUNTIME_ROOT=${PLATFORM_ROOT}/app
MODULES_ROOT=${PLATFORM_ROOT}/src

MODULES["checkout"]["root"]=${MODULES_ROOT}/Marketplace/Checkout

## Help
help:
	printf "${COLOR_COMMENT}Usage:${COLOR_RESET}\n"
	printf " make [target]\n\n"
	printf "${COLOR_COMMENT}Available targets:${COLOR_RESET}\n"
	awk '/^[a-zA-Z\-\_0-9\.@]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf " ${COLOR_INFO}%-16s${COLOR_RESET} %s\n", helpCommand, helpMessage; \
		} \
	} \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)

define exec
	if [ -f /.dockerenv ]; then cd $(1) && $(2); else docker-compose exec -w $(1) app $(2); fi
endef

ifneq (,$(findstring sonar,$(MAKECMDGOALS)))
phpunit_options := $(phpunit_options) --coverage-clover build/reports/coverage.xml --log-junit build/reports/tests.xml
endif

##################
# Useful targets #
##################

copy-env-file:
	@echo Copying environment file...
	@if [ ! -e ./app/.env.local ]; then cp ./app/.env ./app/.env.local; fi

## starts platform
start: copy-env-file
	@echo Running \${COLOR_INFO}\`docker-compose up -d\`\${COLOR_RESET} ...
	docker-compose up -d

## stops running platform `docker-compose stop`
stop:
	@echo Running \${COLOR_INFO}\`docker-compose stop\`\${COLOR_RESET} ...
	docker-compose stop

## starts platform `docker-compose up -d --build`
build: destroy copy-env-file
	@echo Running \${COLOR_INFO}\`docker-compose up -d --build\`\${COLOR_RESET} ...
	docker-compose up -d --build

## destroys platform `docker-compose down --remove-orphans`
destroy:
	@echo Running \${COLOR_INFO}\`docker-compose down --remove-orphans\`\${COLOR_RESET} ...
	docker-compose down --remove-orphans

install-platform-deps:
	$(call exec, ${PLATFORM_RUNTIME_ROOT}, composer install)

## Show routes exposed by the platform
show-routes:
	$(call exec, ${PLATFORM_RUNTIME_ROOT}, ./bin/console debug:router)

########
# Code #
########

## Run cs-fixer to fix php code to follow standards.
cs-fix:
	$(call exec, ${PLATFORM_RUNTIME_ROOT}, ./vendor/bin/php-cs-fixer fix src/)
	$(call exec, ${PLATFORM_RUNTIME_ROOT}, ./vendor/bin/php-cs-fixer fix ${MODULES_ROOT})

## Run PHPStan to find errors in code.
code-static-analysis: install-platform-deps
	$(call exec, ${PLATFORM_RUNTIME_ROOT}, ./vendor/bin/phpstan analyse --level=max src/)

## Run codesniffer to correct violations of a defined coding project standards.
code_correct:
	$(call exec, ${PLATFORM_RUNTIME_ROOT}, ./vendor/bin/phpcs --standard=PSR2 ${MODULES["checkout"]["root"]})

## Run tests+qa
prep: code-static-analysis checkout-static-analysis checkout-tests #cs-fix

##################
# Checkout targets #
##################

install-checkout-deps:
	$(call exec, ${MODULES["checkout"]["root"]}, composer install)

checkout-static-analysis: install-checkout-deps install-platform-deps
	$(call exec, ${MODULES["checkout"]["root"]}, ./vendor/bin/phpstan analyse --level=max --configuration=${MODULES["checkout"]["root"]}/tests/phpstan.neon --autoload-file=${MODULES["checkout"]["root"]}/vendor/autoload.php ${MODULES["checkout"]["root"]}/tests/)
	$(call exec, ${MODULES["checkout"]["root"]}, ./vendor/bin/phpstan analyse --level=max --autoload-file=${PLATFORM_RUNTIME_ROOT}/vendor/autoload.php ${MODULES["checkout"]["root"]}/src/)
	$(call exec, ${MODULES["checkout"]["root"]}, ./vendor/bin/psalm -r /srv/src/Marketplace/Checkout ${MODULES["checkout"]["root"]})

checkout-unit-tests: #install-checkout-deps
	$(call exec, ${MODULES["checkout"]["root"]}, ./vendor/bin/phpunit)

checkout-behavior-tests: #install-checkout-deps
	$(call exec, ${MODULES["checkout"]["root"]}, ./vendor/bin/behat)

checkout-integration-tests: create-test-databases # install-checkout-deps install-platform-deps
	$(call exec, ${PLATFORM_RUNTIME_ROOT}, ./bin/console doctrine:database:drop -e test --connection checkout --force --if-exists)
	$(call exec, ${PLATFORM_RUNTIME_ROOT}, ./bin/console doctrine:database:create -e test --connection checkout)
	$(call exec, ${PLATFORM_RUNTIME_ROOT}, ./bin/console doctrine:schema:create -e test --em checkout)
	$(call exec, ${PLATFORM_RUNTIME_ROOT}, ./bin/console doctrine:fixtures:load --no-interaction --env test --em checkout)
	$(call exec, ${MODULES["checkout"]["root"]}, ./vendor/bin/phpunit -c phpunit.integration.xml)

### Run Checkout tests
checkout-tests: checkout-unit-tests checkout-behavior-tests checkout-integration-tests

## Consume Domain Events
consume-domain-events:
	$(call exec, ${PLATFORM_RUNTIME_ROOT}/bin/console enqueue:setup-broker) # to be moved at broker level
	$(call exec, ${PLATFORM_RUNTIME_ROOT}/bin/console enqueue:transport:consume bus acme.platform.domain-events)

create-test-databases:
	$(call exec, ${PLATFORM_RUNTIME_ROOT}, ./bin/console doctrine:database:drop -e test --force --if-exists)
	$(call exec, ${PLATFORM_RUNTIME_ROOT}, ./bin/console doctrine:database:create -e test)
