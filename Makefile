COMPONENT := pluggithttpclient
CONTAINER := client-php
IMAGES ?= false
PHP_VERSION ?: false
APP_ROOT := /app/http-client

all: dev logs

dev:
	@docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml up -d --build

enter:
	@docker exec -ti ${COMPONENT}_${CONTAINER}_1 /bin/sh

kill:
	@docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml kill

nodev:
	@docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml kill
	@docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml rm -f
ifeq ($(IMAGES),true)
	@docker rmi ${COMPONENT}_${CONTAINER}
endif

test: unit integration
unit:
	make dev
	@docker exec -t $(shell docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml ps -q ${CONTAINER}) \
	 ${APP_ROOT}/ops/scripts/unit.sh ${PHP_VERSION}

integration:
	make dev
	@docker exec -t $(shell docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml ps -q ${CONTAINER}) \
	 ${APP_ROOT}/ops/scripts/integration.sh ${PHP_VERSION}

code-coverage:
	make dev
	@docker exec -t $(shell docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml ps -q ${CONTAINER}) \
	 php-5.6 ${APP_ROOT}/bin/http tests:run html

ps: status
status:
	@docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml ps

logs:
	@docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml logs

tag: # List last tag for this repo
	@git tag -l | sort -r |head -1

restart: nodev dev logs
