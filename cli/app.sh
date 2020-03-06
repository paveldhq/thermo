#!/usr/bin/env bash

CUR_DIR=$(pwd)
CLI_DIR=$(dirname "$0") && cd ${CLI_DIR} && CLI_DIR=$(pwd)
PROJ_DIR="${CLI_DIR}/../" && cd ${PROJ_DIR} && PROJ_DIR=$(pwd)
ENV_DIR="${CLI_DIR}/env" && cd ${ENV_DIR} && ENV_DIR=$(pwd)

. "${PROJ_DIR}/.env"

function checkImage() {
  local IMAGE="$1"
  if [[ -z "$(docker images -q ${IMAGE})" ]]; then
    cd "${ENV_DIR}"
    docker build -t="${IMAGE}" .
  fi
}

PHP_CLI_IMAGE="thermo/cli:${THERMO_VERSION}"

checkImage "${PHP_CLI_IMAGE}"

docker run \
  --rm \
  --name thermo-cli \
  -v ${PROJ_DIR}:/project \
  -e PROJ_DIR="/project" \
  ${PHP_CLI_IMAGE} \
  $@
