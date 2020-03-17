<?php

namespace Thermo\Commands\ArduinoCli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Thermo\Processors\ConfigProcessor;

/**
 * Class ConfigCommand
 * @package Thermo\Commands
 */
class ConfigCommand extends Command
{
    const COMMAND_NAME = 'config';
    use ArduinoCommandTrait;

    /**
     * @var string
     */
    private string $placeholder = '';

    /**
     * @var string
     */
    private string $configFileName = '';

    public function __construct(string $placeholder, string $configFileName)
    {
        parent::__construct(null);
        $this->placeholder    = $placeholder;
        $this->configFileName = $configFileName;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(vsprintf('%s:%s', [$this->commandPrefix, static::COMMAND_NAME]));
        $this
            ->setDescription('Generates yaml config file for {{arduino-cli}}')
            ->addArgument('arduino-dir', InputArgument::REQUIRED, 'arduino base directory');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arduinoDir = $input->getArgument('arduino-dir');
        $output->writeln(vsprintf('Generating yaml file using %s as arduino home dir.', [$arduinoDir]));
        $processor = new ConfigProcessor($this->placeholder, $arduinoDir);
        file_put_contents(vsprintf('%s/%s', [PROJ_DIR, $this->configFileName]), $processor->process());
        return 0;
    }
}
