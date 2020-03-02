#!/usr/bin/env bash

. "$(dirname $0)/.env"

function logStr() {
  SEVERITY=$1
  MESSAGE=$2
  Color_Off='\033[0m'
  IWhite='\033[0;97m'
  echo -e "${IWhite}[${SEVERITY}]\t${Color_Off}${MESSAGE}"
  # remoe later ?
  echo ""
}

function debug() {
  logStr " DBG " "$1"
}

function warn() {
  logStr " WRN " "$1"
}

function error() {
  logStr " ERR " "$1"
}

function checkDocker() {
  local DOCKER_BIN=$(which docker)
  if [[ -z "${DOCKER_BIN}" ]]; then
    error "Docker not found. Please install it running 'curl -L get.docker.com | bash'"
    exit 1
  fi
}

# Save current dir
CUR_DIR=$(pwd)

# Pather placeholders
PROJ_DIR=$(dirname $0) && cd ${PROJ_DIR} && PROJ_DIR=$(pwd)
CLI_DIR=${PROJ_DIR}/cli
ENV_DIR=${CLI_DIR}/env
A_CLI_SRC_DIR=${PROJ_DIR}/build-tools/src
ARDUINO_DIR=${PROJ_DIR}/build-tools/arduino

function getCLI() {
  # First Obtain "kernel" name
  KERNEL=$(uname -s)
  local CLI_EXECUTABLE_BINARY=""
  if [ $KERNEL = "Darwin" ]; then
    CLI_EXECUTABLE_BINARY="${A_CLI_SRC_DIR}/dist/arduino_cli_osx_darwin_amd64/arduino-cli"
  elif [ $Nucleo = "Linux" ]; then
    echo unknown...
    exit 5
    # // unknown ??
  fi
  echo ${CLI_EXECUTABLE_BINARY}
}

CLI_EXECUTABLE_BINARY=$(getCLI)

function checkDependencies() {
  local DEP_UTILS="git grep cut tar curl readlink dirname"
  for DEPENDENCY in $DEP_UTILS; do
    if [[ -z "$(which $DEPENDENCY)" ]]; then
      error "Dependency ${DEPENDENCY} not found. Please install first!"
      exit 1
    fi
  done
}

function goDir() {
  if [[ ! -d "$1" ]]; then
    mkdir -p "$1"
  fi
  cd "$1"
}

function pConsole() {
  local PHP_CLI_IMAGE="thermo/cli:${THERMO_VERSION}"

  if [[ -z "$(docker images -q ${PHP_CLI_IMAGE})" ]]; then
    cd "${ENV_DIR}"
    docker build -t=${PHP_CLI_IMAGE} -f Dockerfile .
  fi

  docker run --rm --name thermo-cli -v ${PROJ_DIR}:/project ${PHP_CLI_IMAGE} $@

}

function tryGetArduinoCli() {
  if [[ ! -d "${A_CLI_SRC_DIR}" ]]; then
    local A_CLI_REPO="arduino/arduino-cli"
    pConsole deploy ${A_CLI_REPO}
  fi
}

function boot() {
  checkDocker
  checkDependencies
}

function init() {

  tryGetArduinoCli

  cd ${A_CLI_SRC_DIR}/Dockerfiles/builder
  docker build -t=arduino/cli:builder .
  echo Moving to ${A_CLI_SRC_DIR}

  cd ${A_CLI_SRC_DIR}
  sleep 3
  docker run --rm -v $PWD:/arduino-cli -w /arduino-cli -e PACKAGE_NAME_PREFIX='snapshot' arduino/cli:builder goreleaser --rm-dist --snapshot --skip-publish

}

boot

if [[ ! -f "${CLI_EXECUTABLE_BINARY}" ]]; then
  init
fi

if [[ ! -d ${ARDUINO_DIR} ]]; then
  mkdir -p "${ARDUINO_DIR}"
  NEED_BOARD_INSTALL="1"
fi

pConsole generate-yaml-config ${ARDUINO_DIR}

A_CLI="${CLI_EXECUTABLE_BINARY} --config-file ${PROJ_DIR}/build-tools/arduino-cli.yaml"

if [[ ${NEED_BOARD_INSTALL} == "1" ]]; then
  ${A_CLI} core update-index
  ${A_CLI} core download esp32:esp32
  ${A_CLI }core download esp8266:esp8266
fi

${A_CLI} "$@"
