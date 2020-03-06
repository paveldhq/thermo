<?php


namespace Thermo\Commands\ArduinoCli;

/**
 * Trait ArduinoCommandTrait
 * @package Thermo\Commands\ArduinoCli
 */
trait ArduinoCommandTrait
{
    /**
     * @var string
     */
    protected string $commandPrefix = 'arduino';

    /**
     * @inheritDoc
     */
    function configure()
    {
        $this->setName(vsprintf('%s:%s', [$this->commandPrefix, static::COMMAND_NAME]));
        parent::configure();
    }


}
