#!/usr/bin/env bash

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

function initLink() {
  cd ${PROJ_DIR}
  if [[ ! -f "thermo.sh" ]]; then
    ln -s "$SCRIPT_DIR/thermo.sh" "./thermo.sh"
    debug "Symbolic link created in '${PROJ_DIR}'. Please use it."
  fi
}

function checkDocker() {
  local DOCKER_BIN=$(which docker)
  if [[ -z "${DOCKER_BIN}" ]]; then
    error "Docker not found. Please install it running 'curl -L get.docker.com | bash'"
    exit 1
  fi
}

function getStoredGitCommitHash() {
  cd ${PROJ_DIR}
  if [[ -f "./.env" ]]; then
    GIT_HASH=$(grep GIT_HASH .env | cut -d '=' -f2)
  fi
}

function validateGitCommit() {
  GIT_HASH_CUR=$(git log --pretty=format:'%h' -n 1)
  debug "Working with git hash ${GIT_HASH_CUR}..."
  if [[ -z "${GIT_HASH}" ]]; then
    echo GIT_HASH=${GIT_HASH_CUR} > ${PROJ_DIR}/.env
  else
    if [[ "${GIT_HASH}" != ${GIT_HASH_CUR} ]]; then
      warn "Current git hash differs from stored. it is recommentded to rebuild environment. Run './thermo.sh --force-reset'. (todo)"
    fi
  fi
}

