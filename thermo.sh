#!/usr/bin/env bash
SELF="$0"
. "$(dirname ${SELF})/.env"

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
ARDUINO_DIR=build-tools/arduino

initOS() {
	OS=$(uname -s)
	case "$OS" in
		Linux*) OS='Linux' ;;
		Darwin*) OS='macOS' ;;
		MINGW*) OS='Windows';;
		MSYS*) OS='Windows';;
	esac
	echo "$OS"
}

function getCLI() {
  OS=$(initOS)
  local CLI_EXECUTABLE_BINARY=""
  case "$OS" in
  'Linux')
    CLI_EXECUTABLE_BINARY="${A_CLI_SRC_DIR}/dist/arduino_cli_linux_amd64/arduino-cli"
    ;;
  'macOS')
    CLI_EXECUTABLE_BINARY="${A_CLI_SRC_DIR}/dist/arduino_cli_osx_darwin_amd64/arduino-cli"
    ;;
  'Windows')
    CLI_EXECUTABLE_BINARY="${A_CLI_SRC_DIR}/dist/arduino_cli_windows_amd64/arduino-cli.exe"
    ;;
  esac

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

function pConsole() {
  ${CLI_DIR}/app.sh $@
  if [[ 0 -lt $? ]]; then
    exit $?
  fi
}

function tryGetArduinoCli() {
  if [[ ! -d "${A_CLI_SRC_DIR}" ]]; then
    local A_CLI_REPO="arduino/arduino-cli"
    pConsole arduino:deploy ${A_CLI_REPO}
  fi
}

function buildArduinoCli() {
  local BUILDER_TAG="arduino/cli:builder"
  if [[ -z "$(docker images -q ${BUILDER_TAG})" ]]; then
    cd "${A_CLI_SRC_DIR}/Dockerfiles/builder"
    docker build -t=${BUILDER_TAG} .
  fi

  echo Moving to ${A_CLI_SRC_DIR}
  cd ${A_CLI_SRC_DIR}
  sleep 3
  docker run --rm -v $PWD:/arduino-cli -w /arduino-cli -e PACKAGE_NAME_PREFIX='snapshot' ${BUILDER_TAG} goreleaser --rm-dist --snapshot --skip-publish
}

function boot() {
  checkDocker
  checkDependencies
}

function init() {
  tryGetArduinoCli
  buildArduinoCli
}

boot

if [[ ! -f "${CLI_EXECUTABLE_BINARY}" ]]; then
  init
fi

if [[ ! -d ${ARDUINO_DIR} ]]; then
  mkdir -p "${ARDUINO_DIR}"
  NEED_BOARD_INSTALL="1"
fi

pConsole arduino:config ${ARDUINO_DIR}
cd ${PROJ_DIR}

A_CLI="${CLI_EXECUTABLE_BINARY} --config-file ${PROJ_DIR}/build-tools/arduino-cli.yaml"
A_CLI="${CLI_EXECUTABLE_BINARY}"

if [[ "$1" == "reset" ]]; then
  debug "Resetting..."
  rm -rf ${PROJ_DIR}/build-tools
  rm -rf ${PROJ_DIR}/cli/vendor
  debug "Done."
  exit 0
fi

function initArduino() {
  ${SELF} core update-index
}

function addBoard() {
  ${SELF} core download "$1"
  ${SELF} core install "$1"
}

if [[ "$1" == 'init-arduino-dir' ]]; then
  initArduino
  ${SELF} add-board esp32:esp32
  ${SELF} add-board esp8266:esp8266
  exit $?
fi

if [[ "$1" == 'add-board' ]]; then
  shift
  addBoard $1
  exit $?
fi

if [[ ${NEED_BOARD_INSTALL} == "1" ]]; then
  ${SELF} init-arduino-dir
fi

if [[ "$1" == "build" ]]; then
  ${SELF} compile --fqbn esp32:esp32:esp32
  exit $?
fi

if [[ "$1" == "install-external-libraries" ]]; then
  for i in $(cat ./dependencies.txt) ; do
    ./cli/app.sh ext-lib:install $i
  done
fi

${A_CLI} $@
