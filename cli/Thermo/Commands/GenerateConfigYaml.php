<?php

namespace Thermo\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class GenerateConfigYaml
 * @package Thermo\Commands
 */
class GenerateConfigYaml extends Command
{
    /**
     * Config template.
     */
    const YAML_CONFIG_TEMPLATE = [
        'board_manager' => [
            'additional_urls' => [
                'https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json',
                'https://arduino.esp8266.com/stable/package_esp8266com_index.json'
            ]
        ],
        'daemon' => [
            'port' => '50051'
        ],
        'directories' => [
            'data' => '%ARDUINO_DIR%',
            'downloads' => '%ARDUINO_DIR%/staging',
            'user' => '%ARDUINO_DIR%'
        ],
        'logging' => [
            'file' => '',
            'format' => 'text',
            'level' => 'info'
        ],
    ];

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('generate-yaml-config')
            ->setDescription('Generates yaml config file for {{arduino-cli}}')
            ->addArgument('arduino-dir', InputArgument::REQUIRED, 'arduino base directory');
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arduinoDir = $input->getArgument('arduino-dir');
        $output->writeln(vsprintf("Generating yaml file using %s as arduino home dir.", [$arduinoDir]));
        $template = static::YAML_CONFIG_TEMPLATE;
        array_walk_recursive($template, function (&$value) use ($arduinoDir) {
            $value = str_replace('%ARDUINO_DIR%', $arduinoDir, $value);
        });
        $yaml = Yaml::dump($template);
        $outputFile = '/project/build-tools/arduino-cli.yaml';
        file_put_contents($outputFile, $yaml);
        return 0;
    }
}
