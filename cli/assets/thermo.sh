#!/usr/bin/env bash

# Save current dir
CUR_DIR=$(pwd)
DIRECT_EXECUTION="0"

# Pather placeholders
PROJ_DIR=""
SCRIPT_DIR=""
CLI_DIR=""
ENV_DIR=""
A_CLI_SRC_DIR=""
ARDUINO_DIR=""

if [[ ! -L "$0" ]]; then
  DIRECT_EXECUTION="1"
fi

function checkDependencies() {
  local DEP_UTILS="git grep cut tar curl readlink dirname"
  for DEPENDENCY in $DEP_UTILS; do
    if [[ -z "$(which $DEPENDENCY)" ]]; then
      echo !!! Dependency ${DEPENDENCY} not found. Please install first!
      exit 1
    fi
  done
}

function includeFunctions() {
  . ${SCRIPT_DIR}/functions.sh
  checkDocker
}

checkDependencies

function initPaths() {
  if [[ "1" == "${DIRECT_EXECUTION}" ]]; then
    SCRIPT_DIR=$(dirname $0)
    cd "$(dirname $0)/../../"
  else
    SCRIPT_DIR=$(dirname $(readlink $0))
    cd "$(dirname $0)"
  fi
  includeFunctions

  PROJ_DIR=$(pwd)
  debug "Using PROJ_DIR=$PROJ_DIR"

  cd cli
  CLI_DIR=$(pwd)
  debug "Using CLI_DIR=$CLI_DIR"

  cd env
  ENV_DIR=$(pwd)
  debug "Using ENV_DIR=$ENV_DIR"

  cd ${PROJ_DIR}/build-tools
  A_CLI_SRC_DIR=$(pwd)/src
  debug "Using A_CLI_SRC_DIR=$A_CLI_SRC_DIR"

  ARDUINO_DIR=${PROJ_DIR}/arduino
  debug "Using ARDUINO_DIR=$ARDUINO_DIR"

}

function init() {
  initPaths

  if [[ "1" == "${DIRECT_EXECUTION}" ]]; then
    initLink
    exit
  fi

  getStoredGitCommitHash
  validateGitCommit

  if [[ ! -d "${A_CLI_SRC_DIR}" ]]; then
    cd ${ENV_DIR}
    A_CLI_REPO="arduino/arduino-cli"

    docker build -t=thermo/cli:alpha -f ${ENV_DIR}/Dockerfile .
    docker run --rm --name thermo-cli -v ${PROJ_DIR}:/project thermo/cli:alpha deploy ${A_CLI_REPO}
  fi

  # First Obtain "kernel" name
  KERNEL=$(uname -s)
  CLI_EXECUTABLE_BINARY=""
  if [ $KERNEL = "Darwin" ]; then
    CLI_EXECUTABLE_BINARY="${A_CLI_SRC_DIR}/dist/arduino_cli_osx_darwin_amd64/arduino-cli"
  elif [ $Nucleo = "Linux" ]; then
    echo unknown...
    exit 5
    # // unknown ??
  fi

  if [[ ! -f "${CLI_EXECUTABLE_BINARY}" ]]; then
    cd ${A_CLI_SRC_DIR}/Dockerfiles/builder
    docker build -t=arduino/cli:builder .
    cd ${A_CLI_SRC_DIR}
    #docker run --rm -v $PWD:/arduino-cli -w /arduino-cli -e PACKAGE_NAME_PREFIX='snapshot' arduino/cli:builder goreleaser --rm-dist --snapshot --skip-publish
  fi

  debug "Generating config file..."

  cd ${ENV_DIR}
  docker build -t=thermo/cli:alpha -f ${ENV_DIR}/Dockerfile .
  docker run --rm --name thermo-cli -v ${PROJ_DIR}:/project thermo/cli:alpha generate-yaml-config ${ARDUINO_DIR}

  if [[ ! -d ${ARDUINO_DIR} ]]; then
    mkdir -p "${ARDUINO_DIR}"
    NEED_BOARD_INSTALL=1
  fi

  A_CLI="${CLI_EXECUTABLE_BINARY} --config-file ${PROJ_DIR}/build-tools/arduino-cli.yaml"
  echo Updating board index...
  ${A_CLI} core update-index

  if [[ ${NEED_BOARD_INSTALL} == 1 ]]; then
    echo ESP32 board downloading...
    ${A_CLI} core download esp32:esp32
  else
    echo Trying to update ESP32 core...
    ${A_CLI} core upgrade
  fi

  ${A_CLI} $@

}

init
