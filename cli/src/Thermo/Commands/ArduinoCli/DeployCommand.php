<?php

namespace Thermo\Commands\ArduinoCli;

use Thermo\Commands\RepositoryCommandAbstract;

/**
 * Class DeployCommand
 * @package Thermo\Commands
 */
class DeployCommand extends RepositoryCommandAbstract
{
    const COMMAND_NAME = 'deploy';
    use ArduinoCommandTrait;
}
