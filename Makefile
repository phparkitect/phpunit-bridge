.PHONY: help test csfix cs psalm build
.DEFAULT_GOAL := help

help: ## it shows help menu
	@awk 'BEGIN {FS = ":.*#"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z0-9_-]+:.*?#/ { printf "  \033[36m%-27s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

test: ## it launches tests
	bin/phpunit

%Test: ## it launches a single test class
	bin/phpunit --filter $@

cs: ## it checks the coding standard without fixing
	bin/php-cs-fixer check --diff

csfix: ## it fixes the coding standard
	bin/php-cs-fixer fix -v

psalm: ## it launches psalm
	composer install -d tools/psalm
	tools/psalm/vendor/bin/psalm --no-cache

build: ## it launches the whole build
	composer install
	$(MAKE) csfix
	$(MAKE) psalm
	$(MAKE) test
