# cli

A small console application written in PHP that allows to:
 - download {arduino-cli} sources (latest or specific version, latest default)
 - generate {arduino-cli.yml} file
 - download and install any public repository from github by tag*
 
## Notes:
 All calls to console application are executed through `app.sh` shell wrapper, it uses `docker` to execute PHP code.
 
## Downloading {arduino-cli} sources
 In order to download {arduino-cli} sources to designed location (build-tools/src) next command should be used:
`./cli/app.sh arduino:deploy arduino/arduino-cli`

## Downloading libraries from github
 To install third-party library that is not in standard arduino library list next command should be used:
 `./cli/app.sh ext-lib:install <github_user>/<repository_name>`, e.g.:
 `./cli/app.sh ext-lib:install me-no-dev/AsyncTCP`.
 Useful libraries:
 * ayushsharma82/AsyncElegantOTA
 * me-no-dev/ESPAsyncWebServer
 * me-no-dev/ESPAsyncTCP
 * me-no-dev/AsyncTCP
 * bblanchon/ArduinoJson
