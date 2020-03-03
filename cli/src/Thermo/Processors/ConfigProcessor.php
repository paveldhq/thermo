<?php

namespace Thermo\Processors;

use Symfony\Component\Yaml\Yaml;

class ConfigProcessor implements ProcessorInterface
{
    /**
     * @var string
     */
    private string $placeholder;

    /**
     * @var string
     */
    private string $replacement;

    public function __construct(string $placeholder, string $replacement)
    {
        $this->placeholder = $placeholder;
        $this->replacement = $replacement;
    }

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
            'data' => '%ARDUINO_DIR%/data',
            'downloads' => '%ARDUINO_DIR%/staging',
            'user' => '%ARDUINO_DIR%/user'
        ],
        'logging' => [
            'file' => '',
            'format' => 'text',
            'level' => 'info'
        ],
    ];

    /**
     * @param $value
     */
    public function walkerHandler(&$value): void
    {
        $value = str_replace($this->placeholder, $this->replacement, $value);
    }

    /**
     * @return string
     */
    public function process(): string
    {
        $configArray = static::YAML_CONFIG_TEMPLATE;
        array_walk_recursive($configArray, [$this, 'walkerHandler']);
        return Yaml::dump($configArray);
    }
}
